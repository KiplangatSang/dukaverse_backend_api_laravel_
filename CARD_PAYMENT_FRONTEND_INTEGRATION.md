# Card Payment Frontend Integration Guide

## Overview

This guide provides step-by-step instructions for integrating card payments into your frontend application using Stripe Elements and the card payment API.

## Prerequisites

1. **Stripe Account**: Create a Stripe account at https://stripe.com
2. **API Keys**: Obtain publishable and secret keys from Stripe Dashboard
3. **Backend Setup**: Ensure the card payment API endpoints are configured
4. **HTTPS**: Required for production (Stripe requires HTTPS)

## Installation

### 1. Install Dependencies

#### For Vanilla JavaScript
```html
<script src="https://js.stripe.com/v3/"></script>
```

#### For React
```bash
npm install @stripe/stripe-js @stripe/react-stripe-js
```

#### For Vue.js
```bash
npm install vue-stripe-elements-plus
```

#### For Angular
```bash
npm install ngx-stripe
```

## Basic Implementation

### 1. Initialize Stripe

```javascript
// Replace with your Stripe publishable key
const stripe = Stripe('<your-stripe-secret-key>');
const elements = stripe.elements();
```

### 2. Create Card Element

```html
<div id="payment-form">
  <div id="card-element"></div>
  <button id="submit-button">Pay $10.00</button>
  <div id="card-errors" role="alert"></div>
</div>
```

```javascript
// Create card element
const cardElement = elements.create('card', {
  style: {
    base: {
      fontSize: '16px',
      color: '#32325d',
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSmoothing: 'antialiased',
      '::placeholder': {
        color: '#aab7c4'
      }
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a'
    }
  }
});

// Mount the element
cardElement.mount('#card-element');
```

### 3. Handle Form Submission

```javascript
const form = document.getElementById('payment-form');
const submitButton = document.getElementById('submit-button');
const cardErrors = document.getElementById('card-errors');

form.addEventListener('submit', async (event) => {
  event.preventDefault();

  submitButton.disabled = true;
  submitButton.textContent = 'Processing...';

  try {
    // Create payment method
    const { error, paymentMethod } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardElement,
    });

    if (error) {
      cardErrors.textContent = error.message;
      submitButton.disabled = false;
      submitButton.textContent = 'Pay $10.00';
      return;
    }

    // Send to your backend
    const response = await fetch('/api/v1/payments/card', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
      body: JSON.stringify({
        amount: 10.00,
        currency: 'USD',
        payment_method_id: paymentMethod.id,
        description: 'Test payment',
        payer_id: currentUser.id,
        payer_type: 'App\\Models\\User',
        payee_id: merchantId,
        payee_type: 'App\\Models\\User'
      })
    });

    const result = await response.json();

    if (result.success) {
      // Success handling
      showSuccessMessage('Payment successful!');
      redirectToSuccessPage();
    } else {
      // Error handling
      cardErrors.textContent = result.error || 'Payment failed';
    }

  } catch (error) {
    cardErrors.textContent = 'An error occurred. Please try again.';
    console.error('Payment error:', error);
  }

  submitButton.disabled = false;
  submitButton.textContent = 'Pay $10.00';
});
```

## Advanced Implementation

### 1. Card Element Customization

```javascript
const cardElement = elements.create('card', {
  style: {
    base: {
      fontSize: '16px',
      color: '#424770',
      letterSpacing: '0.025em',
      fontFamily: 'Source Code Pro, monospace',
      '::placeholder': {
        color: '#aab7c4',
      },
    },
    invalid: {
      color: '#9e2146',
    },
  },
  hidePostalCode: false,
  iconStyle: 'solid'
});
```

### 2. Real-time Validation

```javascript
cardElement.on('change', (event) => {
  const displayError = document.getElementById('card-errors');

  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }

  // Enable/disable submit button
  const submitButton = document.getElementById('submit-button');
  submitButton.disabled = !event.complete;
});
```

### 3. Loading States

```javascript
function setLoading(isLoading) {
  const submitButton = document.getElementById('submit-button');

  if (isLoading) {
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';
  } else {
    submitButton.disabled = false;
    submitButton.textContent = 'Pay $10.00';
  }
}
```

## Framework-Specific Examples

### React Implementation

```jsx
import React, { useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const stripePromise = loadStripe('pk_test_...');

const CheckoutForm = ({ amount, onSuccess, onError }) => {
  const stripe = useStripe();
  const elements = useElements();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const handleSubmit = async (event) => {
    event.preventDefault();

    if (!stripe || !elements) return;

    setLoading(true);
    setError(null);

    const cardElement = elements.getElement(CardElement);

    try {
      const { error, paymentMethod } = await stripe.createPaymentMethod({
        type: 'card',
        card: cardElement,
      });

      if (error) {
        setError(error.message);
        setLoading(false);
        return;
      }

      const response = await fetch('/api/v1/payments/card', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: JSON.stringify({
          amount: amount,
          currency: 'USD',
          payment_method_id: paymentMethod.id,
          description: 'Payment via React',
          payer_id: currentUser.id,
          payer_type: 'App\\Models\\User'
        })
      });

      const result = await response.json();

      if (result.success) {
        onSuccess(result.data);
      } else {
        onError(result.error || 'Payment failed');
      }
    } catch (err) {
      onError('An error occurred. Please try again.');
    }

    setLoading(false);
  };

  return (
    <form onSubmit={handleSubmit}>
      <div className="card-element-container">
        <CardElement
          options={{
            style: {
              base: {
                fontSize: '16px',
                color: '#424770',
                '::placeholder': {
                  color: '#aab7c4',
                },
              },
              invalid: {
                color: '#9e2146',
              },
            },
          }}
        />
      </div>

      {error && <div className="error-message">{error}</div>}

      <button
        type="submit"
        disabled={!stripe || loading}
        className="pay-button"
      >
        {loading ? 'Processing...' : `Pay $${amount}`}
      </button>
    </form>
  );
};

const PaymentPage = () => (
  <Elements stripe={stripePromise}>
    <CheckoutForm
      amount={10.00}
      onSuccess={(data) => console.log('Success:', data)}
      onError={(error) => console.error('Error:', error)}
    />
  </Elements>
);
```

### Vue.js Implementation

```vue
<template>
  <div class="payment-form">
    <form @submit.prevent="handleSubmit">
      <div id="card-element"></div>
      <button :disabled="loading" type="submit">
        {{ loading ? 'Processing...' : 'Pay $10.00' }}
      </button>
      <div v-if="error" class="error">{{ error }}</div>
    </form>
  </div>
</template>

<script>
import { loadStripe } from '@stripe/stripe-js';

export default {
  name: 'PaymentForm',
  data() {
    return {
      stripe: null,
      cardElement: null,
      loading: false,
      error: null
    };
  },
  async mounted() {
    this.stripe = await loadStripe('pk_test_...');
    const elements = this.stripe.elements();

    this.cardElement = elements.create('card', {
      style: {
        base: {
          fontSize: '16px',
          color: '#32325d',
        }
      }
    });

    this.cardElement.mount('#card-element');

    this.cardElement.on('change', (event) => {
      this.error = event.error ? event.error.message : '';
    });
  },
  methods: {
    async handleSubmit() {
      this.loading = true;
      this.error = null;

      try {
        const { error, paymentMethod } = await this.stripe.createPaymentMethod({
          type: 'card',
          card: this.cardElement,
        });

        if (error) {
          this.error = error.message;
          this.loading = false;
          return;
        }

        const response = await fetch('/api/v1/payments/card', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          },
          body: JSON.stringify({
            amount: 10.00,
            currency: 'USD',
            payment_method_id: paymentMethod.id,
            description: 'Vue.js payment'
          })
        });

        const result = await response.json();

        if (result.success) {
          this.$emit('payment-success', result.data);
        } else {
          this.error = result.error || 'Payment failed';
        }
      } catch (error) {
        this.error = 'An error occurred. Please try again.';
      }

      this.loading = false;
    }
  }
};
</script>
```

## Styling

### CSS for Card Elements

```css
.StripeElement {
  box-sizing: border-box;
  height: 40px;
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: white;
  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}

.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
  border-color: #fa755a;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}

.pay-button {
  background: #5469d4;
  color: #ffffff;
  border: 0;
  padding: 12px 16px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: block;
  width: 100%;
  margin-top: 10px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.pay-button:hover {
  background: #5055b5;
}

.pay-button:disabled {
  background: #cccccc;
  cursor: not-allowed;
}

.error {
  color: #fa755a;
  font-size: 14px;
  margin-top: 5px;
}
```

## Error Handling

### Comprehensive Error Handling

```javascript
function handlePaymentError(error) {
  const errorMessages = {
    'card_declined': 'Your card was declined. Please try a different card.',
    'expired_card': 'Your card has expired. Please use a different card.',
    'incorrect_cvc': 'The CVC number is incorrect. Please check and try again.',
    'processing_error': 'An error occurred while processing your payment. Please try again.',
    'incorrect_number': 'The card number is incorrect. Please check and try again.',
  };

  return errorMessages[error.code] || error.message || 'An unknown error occurred.';
}

// Usage
try {
  // Payment logic
} catch (error) {
  const userFriendlyMessage = handlePaymentError(error);
  displayError(userFriendlyMessage);
}
```

## Testing

### Test Card Numbers

```javascript
const testCards = {
  success: '4242424242424242',
  decline: '4000000000000002',
  insufficient: '4000000000009995',
  expired: '4000000000000069'
};
```

### Test Implementation

```javascript
// Add test mode indicator
const isTestMode = window.location.hostname === 'localhost';

if (isTestMode) {
  console.log('Running in test mode. Use test card numbers.');
  // Show test card numbers in UI
}
```

## Security Best Practices

### 1. Never Store Card Details

```javascript
// ❌ Don't do this
localStorage.setItem('card_number', '4242424242424242');

// ✅ Do this - let Stripe handle it
const { paymentMethod } = await stripe.createPaymentMethod({
  type: 'card',
  card: cardElement,
});
```

### 2. Use HTTPS

```javascript
// Check if running on HTTPS
if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
  console.warn('Stripe requires HTTPS in production');
}
```

### 3. Validate Amounts

```javascript
function validateAmount(amount) {
  const numAmount = parseFloat(amount);

  if (isNaN(numAmount) || numAmount < 0.01 || numAmount > 999999.99) {
    throw new Error('Invalid payment amount');
  }

  return numAmount;
}
```

## Production Deployment

### 1. Environment Variables

```javascript
// config.js
const config = {
  stripe: {
    publishableKey: process.env.NODE_ENV === 'production'
      ? process.env.REACT_APP_STRIPE_PUBLISHABLE_KEY_LIVE
      : process.env.REACT_APP_STRIPE_PUBLISHABLE_KEY_TEST
  }
};
```

### 2. Build Optimization

```javascript
// webpack.config.js - Code splitting for Stripe
import(/* webpackChunkName: "stripe" */ '@stripe/stripe-js').then(({ loadStripe }) => {
  // Load Stripe only when needed
});
```

### 3. Monitoring

```javascript
// Track payment events
const trackPaymentEvent = (event, data) => {
  if (window.gtag) {
    window.gtag('event', event, {
      event_category: 'payment',
      ...data
    });
  }
};

// Usage
trackPaymentEvent('payment_started', { amount: 10.00 });
trackPaymentEvent('payment_completed', { transaction_id: 'txn_123' });
```

## Troubleshooting

### Common Issues

1. **"Invalid API Key"**
   - Check if you're using the correct publishable key
   - Ensure the key matches your environment (test/live)

2. **"IntegrationError: Invalid value for createPaymentMethod()"**
   - Verify the card element is properly mounted
   - Check if Stripe.js is loaded before creating elements

3. **"NetworkError"**
   - Ensure your API endpoint is accessible
   - Check CORS configuration
   - Verify authentication headers

4. **"Your card was declined"**
   - Use test card numbers in development
   - Check Stripe dashboard for declined payment details

### Debug Mode

```javascript
// Enable debug logging
const stripe = Stripe('pk_test_...', {
  apiVersion: '2020-08-27',
});

// Log all Stripe events
stripe.on('ready', () => console.log('Stripe ready'));
stripe.on('error', (error) => console.error('Stripe error:', error));
```

## Support Resources

- [Stripe Documentation](https://stripe.com/docs)
- [Stripe Testing Guide](https://stripe.com/docs/testing)
- [Stripe Elements Reference](https://stripe.com/docs/stripe-js)
- [Community Forums](https://stripe.com/docs/questions)

## Changelog

- **v1.0.0**: Initial release with basic card payment integration
- **v1.1.0**: Added React and Vue.js examples
- **v1.2.0**: Enhanced error handling and security features
- **v1.3.0**: Added comprehensive testing and production deployment guides
