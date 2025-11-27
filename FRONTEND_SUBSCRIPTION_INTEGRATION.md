# Frontend Subscription Management Integration Guide

## Overview

This guide provides comprehensive frontend integration examples for the Laravel Subscription Management System. The backend provides RESTful APIs for managing subscriptions, tiers, coupons, and billing cycles.

## Table of Contents

1. [API Endpoints Overview](#api-endpoints-overview)
2. [Tier Selection and Subscription Flow](#tier-selection-and-subscription-flow)
3. [React Integration Examples](#react-integration-examples)
4. [Vue.js Integration Examples](#vuejs-integration-examples)
5. [Subscription Status Management](#subscription-status-management)
6. [Billing Integration](#billing-integration)
7. [Webhook Handling](#webhook-handling)
8. [Error Handling & Troubleshooting](#error-handling--troubleshooting)

## API Endpoints Overview

### Authentication
All API requests require Bearer token authentication:
```
Authorization: Bearer {your_jwt_token}
```

### Base URL
```
https://your-domain.com/api
```

### Key Endpoints

#### Subscriptions
- `GET /api/subscriptions` - List all subscriptions
- `POST /api/subscriptions` - Create subscription
- `GET /api/subscriptions/{id}` - Get subscription details
- `PUT /api/subscriptions/{id}` - Update subscription
- `DELETE /api/subscriptions/{id}` - Cancel subscription
- `POST /api/subscriptions/{id}/reactivate` - Reactivate subscription
- `POST /api/subscriptions/{id}/extend-trial` - Extend trial
- `GET /api/subscriptions/user/{userId}` - Get user subscriptions
- `GET /api/subscriptions/active` - Get active subscriptions

#### Tiers
- `GET /api/tiers` - List all tiers
- `POST /api/tiers` - Create tier
- `PUT /api/tiers/{id}` - Update tier
- `DELETE /api/tiers/{id}` - Delete tier

#### Coupons
- `GET /api/coupons` - List all coupons
- `POST /api/coupons` - Create coupon
- `PUT /api/coupons/{id}` - Update coupon
- `DELETE /api/coupons/{id}` - Delete coupon
- `POST /api/coupons/validate` - Validate coupon

## Tier Selection and Subscription Flow

### How Users Subscribe to Tiers

The subscription process involves several key steps:

1. **Display Available Tiers** - Fetch and display all active tiers with their pricing
2. **Tier Selection** - Allow users to compare and select a tier
3. **Subscription Configuration** - Configure auto-renewal, apply coupons
4. **Payment Processing** - Handle payment through integrated payment gateway
5. **Subscription Creation** - Create the subscription record
6. **Confirmation** - Show success message and subscription details

### Tier Display Component

```jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const TierSelection = ({ onTierSelect }) => {
  const [tiers, setTiers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedTier, setSelectedTier] = useState(null);

  useEffect(() => {
    fetchTiers();
  }, []);

  const fetchTiers = async () => {
    try {
      const response = await axios.get('/api/tiers');
      // Filter only active tiers and sort by price
      const activeTiers = response.data.data.tiers
        .filter(tier => tier.is_active)
        .sort((a, b) => a.price - b.price);

      setTiers(activeTiers);
    } catch (error) {
      console.error('Error fetching tiers:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleTierSelect = (tier) => {
    setSelectedTier(tier);
    onTierSelect(tier);
  };

  const getBillingLabel = (billingDuration) => {
    const labels = {
      'month': 'per month',
      'year': 'per year',
      '6 months': 'every 6 months',
      'week': 'per week',
      'day': 'per day'
    };
    return labels[billingDuration] || 'per month';
  };

  if (loading) return <div>Loading tiers...</div>;

  return (
    <div className="tier-selection">
      <h2 className="text-2xl font-bold mb-6">Choose Your Plan</h2>

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {tiers.map((tier) => (
          <div
            key={tier.id}
            className={`border rounded-lg p-6 cursor-pointer transition-all ${
              selectedTier?.id === tier.id
                ? 'border-blue-500 bg-blue-50'
                : 'border-gray-200 hover:border-gray-300'
            }`}
            onClick={() => handleTierSelect(tier)}
          >
            {tier.is_recommended && (
              <div className="bg-blue-500 text-white text-xs px-2 py-1 rounded mb-3 inline-block">
                RECOMMENDED
              </div>
            )}

            <h3 className="text-xl font-semibold mb-2">{tier.name}</h3>
            <p className="text-gray-600 mb-4">{tier.description}</p>

            <div className="mb-4">
              <span className="text-3xl font-bold">${tier.price}</span>
              <span className="text-gray-500">/{getBillingLabel(tier.billing_duration)}</span>
            </div>

            {tier.trial_period_days > 0 && (
              <div className="text-green-600 font-medium mb-3">
                {tier.trial_period_days} days free trial
              </div>
            )}

            <div className="text-sm text-gray-600">
              <div className="font-medium mb-2">Features:</div>
              <ul className="space-y-1">
                {tier.benefits && Object.entries(tier.benefits).map(([key, value]) => (
                  <li key={key} className="flex items-center">
                    <span className="text-green-500 mr-2">✓</span>
                    {key}: {value}
                  </li>
                ))}
              </ul>
            </div>
          </div>
        ))}
      </div>

      {selectedTier && (
        <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
          <h3 className="font-semibold">Selected: {selectedTier.name}</h3>
          <p className="text-sm text-gray-600">
            ${selectedTier.price} {getBillingLabel(selectedTier.billing_duration)}
            {selectedTier.trial_period_days > 0 && ` with ${selectedTier.trial_period_days} days free trial`}
          </p>
        </div>
      )}
    </div>
  );
};

export default TierSelection;
```

### Complete Subscription Flow Component

```jsx
import React, { useState } from 'react';
import axios from 'axios';
import TierSelection from './TierSelection';
import CouponValidation from './CouponValidation';
import PaymentForm from './PaymentForm';

const SubscriptionFlow = () => {
  const [currentStep, setCurrentStep] = useState(1);
  const [selectedTier, setSelectedTier] = useState(null);
  const [couponData, setCouponData] = useState(null);
  const [subscriptionData, setSubscriptionData] = useState({
    auto_renewal: true,
    payment_method: 'stripe'
  });
  const [loading, setLoading] = useState(false);

  const steps = [
    { id: 1, title: 'Choose Plan', component: 'tier' },
    { id: 2, title: 'Apply Coupon', component: 'coupon' },
    { id: 3, title: 'Payment', component: 'payment' },
    { id: 4, title: 'Confirmation', component: 'confirmation' }
  ];

  const handleTierSelect = (tier) => {
    setSelectedTier(tier);
    setSubscriptionData(prev => ({
      ...prev,
      tier_id: tier.id,
      subscription_price: tier.price
    }));
  };

  const handleCouponApplied = (couponResult) => {
    setCouponData(couponResult);
    setSubscriptionData(prev => ({
      ...prev,
      coupon_code: couponResult.coupon.code,
      discounted_price: couponResult.finalAmount
    }));
  };

  const handlePaymentSuccess = async (paymentData) => {
    setLoading(true);
    try {
      // Create the subscription
      const response = await axios.post('/api/subscriptions', subscriptionData, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });

      setCurrentStep(4); // Go to confirmation
    } catch (error) {
      console.error('Error creating subscription:', error);
      alert('Failed to create subscription');
    } finally {
      setLoading(false);
    }
  };

  const nextStep = () => {
    if (currentStep < steps.length) {
      setCurrentStep(currentStep + 1);
    }
  };

  const prevStep = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1);
    }
  };

  const renderStepContent = () => {
    switch (currentStep) {
      case 1:
        return (
          <TierSelection
            onTierSelect={(tier) => {
              handleTierSelect(tier);
              nextStep();
            }}
          />
        );

      case 2:
        return (
          <CouponValidation
            tierId={selectedTier?.id}
            amount={selectedTier?.price}
            onCouponApplied={handleCouponApplied}
            onSkip={nextStep}
          />
        );

      case 3:
        return (
          <PaymentForm
            amount={couponData?.finalAmount || selectedTier?.price}
            tier={selectedTier}
            onPaymentSuccess={handlePaymentSuccess}
            onBack={prevStep}
            loading={loading}
          />
        );

      case 4:
        return (
          <SubscriptionConfirmation
            tier={selectedTier}
            couponData={couponData}
            onComplete={() => window.location.href = '/subscriptions'}
          />
        );

      default:
        return null;
    }
  };

  return (
    <div className="max-w-4xl mx-auto p-6">
      {/* Progress Indicator */}
      <div className="mb-8">
        <div className="flex justify-between items-center">
          {steps.map((step) => (
            <div key={step.id} className="flex items-center">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${
                step.id <= currentStep
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-200 text-gray-600'
              }`}>
                {step.id}
              </div>
              <span className={`ml-2 text-sm ${step.id <= currentStep ? 'text-blue-600 font-medium' : 'text-gray-500'}`}>
                {step.title}
              </span>
              {step.id < steps.length && (
                <div className={`w-12 h-0.5 mx-4 ${
                  step.id < currentStep ? 'bg-blue-600' : 'bg-gray-200'
                }`} />
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Step Content */}
      <div className="bg-white rounded-lg shadow-md p-6">
        {renderStepContent()}
      </div>
    </div>
  );
};

export default SubscriptionFlow;
```

### Coupon Validation Component

```jsx
import React, { useState } from 'react';
import axios from 'axios';

const CouponValidation = ({ tierId, amount, onCouponApplied, onSkip }) => {
  const [couponCode, setCouponCode] = useState('');
  const [validating, setValidating] = useState(false);
  const [couponResult, setCouponResult] = useState(null);

  const validateCoupon = async () => {
    if (!couponCode.trim()) return;

    setValidating(true);
    try {
      const response = await axios.post('/api/coupons/validate', {
        code: couponCode,
        tier_id: tierId,
        amount: amount
      });

      const result = response.data.data;
      setCouponResult(result);
      onCouponApplied(result);
    } catch (error) {
      console.error('Coupon validation failed:', error);
      setCouponResult({ error: 'Invalid coupon code' });
    } finally {
      setValidating(false);
    }
  };

  const applyCoupon = () => {
    if (couponResult && couponResult.is_valid) {
      onCouponApplied(couponResult);
    }
  };

  return (
    <div className="max-w-md mx-auto">
      <h2 className="text-2xl font-bold mb-4">Apply Coupon (Optional)</h2>

      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-2">Coupon Code</label>
          <div className="flex space-x-2">
            <input
              type="text"
              value={couponCode}
              onChange={(e) => setCouponCode(e.target.value.toUpperCase())}
              className="flex-1 p-2 border rounded"
              placeholder="Enter coupon code"
            />
            <button
              onClick={validateCoupon}
              disabled={validating || !couponCode.trim()}
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
            >
              {validating ? 'Validating...' : 'Validate'}
            </button>
          </div>
        </div>

        {couponResult && (
          <div className={`p-4 rounded ${couponResult.is_valid ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`}>
            {couponResult.is_valid ? (
              <div>
                <h3 className="font-medium text-green-800">Coupon Applied!</h3>
                <p className="text-sm text-green-700 mt-1">
                  Original Price: ${amount}<br/>
                  Discount: -${couponResult.discount}<br/>
                  <strong>Final Price: ${couponResult.final_amount}</strong>
                </p>
                <button
                  onClick={applyCoupon}
                  className="mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                >
                  Apply Coupon
                </button>
              </div>
            ) : (
              <div>
                <h3 className="font-medium text-red-800">Invalid Coupon</h3>
                <p className="text-sm text-red-700 mt-1">
                  {couponResult.error || 'This coupon is not valid for your selection.'}
                </p>
              </div>
            )}
          </div>
        )}

        <div className="flex justify-between pt-4">
          <button
            onClick={onSkip}
            className="px-6 py-2 border border-gray-300 rounded hover:bg-gray-50"
          >
            Skip Coupon
          </button>
          {couponResult?.is_valid && (
            <button
              onClick={applyCoupon}
              className="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Continue with Discount
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default CouponValidation;
```

### Payment Form Component

```jsx
import React, { useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const stripePromise = loadStripe(process.env.REACT_APP_STRIPE_PUBLISHABLE_KEY);

const PaymentFormContent = ({ amount, tier, onPaymentSuccess, onBack }) => {
  const stripe = useStripe();
  const elements = useElements();
  const [processing, setProcessing] = useState(false);
  const [billingDetails, setBillingDetails] = useState({
    name: '',
    email: ''
  });

  const handleSubmit = async (event) => {
    event.preventDefault();

    if (!stripe || !elements) return;

    setProcessing(true);

    try {
      // Create payment intent
      const response = await axios.post('/api/create-payment-intent', {
        amount: amount * 100, // Convert to cents
        tier_id: tier.id,
        billing_details: billingDetails
      });

      const { clientSecret } = response.data;

      // Confirm payment
      const { error } = await stripe.confirmCardPayment(clientSecret, {
        payment_method: {
          card: elements.getElement(CardElement),
          billing_details: billingDetails
        }
      });

      if (error) {
        console.error('Payment failed:', error);
        alert('Payment failed: ' + error.message);
      } else {
        onPaymentSuccess({ paymentIntent: result.paymentIntent });
      }
    } catch (error) {
      console.error('Payment error:', error);
      alert('Payment processing failed');
    } finally {
      setProcessing(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-md mx-auto">
      <h2 className="text-2xl font-bold mb-4">Complete Payment</h2>

      <div className="bg-gray-50 p-4 rounded mb-6">
        <h3 className="font-medium mb-2">Order Summary</h3>
        <p>{tier.name} Plan</p>
        <p className="text-xl font-bold">${amount}</p>
      </div>

      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Full Name</label>
          <input
            type="text"
            value={billingDetails.name}
            onChange={(e) => setBillingDetails({...billingDetails, name: e.target.value})}
            className="w-full p-2 border rounded"
            required
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">Email</label>
          <input
            type="email"
            value={billingDetails.email}
            onChange={(e) => setBillingDetails({...billingDetails, email: e.target.value})}
            className="w-full p-2 border rounded"
            required
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">Card Information</label>
          <div className="p-3 border rounded">
            <CardElement options={{
              style: {
                base: {
                  fontSize: '16px',
                  color: '#424770',
                  '::placeholder': {
                    color: '#aab7c4',
                  },
                },
              },
            }} />
          </div>
        </div>

        <div className="flex space-x-4">
          <button
            type="button"
            onClick={onBack}
            className="flex-1 px-4 py-2 border border-gray-300 rounded hover:bg-gray-50"
            disabled={processing}
          >
            Back
          </button>
          <button
            type="submit"
            disabled={!stripe || processing}
            className="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
          >
            {processing ? 'Processing...' : `Pay $${amount}`}
          </button>
        </div>
      </div>
    </form>
  );
};

const PaymentForm = (props) => (
  <Elements stripe={stripePromise}>
    <PaymentFormContent {...props} />
  </Elements>
);

export default PaymentForm;
```

### Subscription Confirmation Component

```jsx
import React from 'react';

const SubscriptionConfirmation = ({ tier, couponData, onComplete }) => {
  const finalAmount = couponData?.finalAmount || tier.price;

  return (
    <div className="max-w-md mx-auto text-center">
      <div className="mb-6">
        <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 className="text-2xl font-bold text-green-600 mb-2">Subscription Activated!</h2>
        <p className="text-gray-600">Your subscription has been successfully created.</p>
      </div>

      <div className="bg-gray-50 p-6 rounded mb-6">
        <h3 className="font-medium mb-4">Subscription Details</h3>
        <div className="space-y-2 text-left">
          <div className="flex justify-between">
            <span>Plan:</span>
            <span className="font-medium">{tier.name}</span>
          </div>
          <div className="flex justify-between">
            <span>Price:</span>
            <span>${tier.price}</span>
          </div>
          {couponData && (
            <div className="flex justify-between text-green-600">
              <span>Discount:</span>
              <span>-${couponData.discount}</span>
            </div>
          )}
          <div className="border-t pt-2 flex justify-between font-bold">
            <span>Total Paid:</span>
            <span>${finalAmount}</span>
          </div>
          {tier.trial_period_days > 0 && (
            <div className="text-green-600 text-sm mt-2">
              🎉 {tier.trial_period_days} days free trial activated!
            </div>
          )}
        </div>
      </div>

      <div className="space-y-3">
        <button
          onClick={onComplete}
          className="w-full px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium"
        >
          Go to My Subscriptions
        </button>
        <p className="text-sm text-gray-500">
          A confirmation email has been sent to your email address.
        </p>
      </div>
    </div>
  );
};

export default SubscriptionConfirmation;
```

## React Integration Examples

### 1. Subscription List Component

```jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const SubscriptionList = () => {
  const [subscriptions, setSubscriptions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchSubscriptions();
  }, []);

  const fetchSubscriptions = async () => {
    try {
      const response = await axios.get('/api/subscriptions', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      setSubscriptions(response.data.data.subscriptions);
    } catch (err) {
      setError('Failed to fetch subscriptions');
      console.error('Error fetching subscriptions:', err);
    } finally {
      setLoading(false);
    }
  };

  const getStatusBadge = (subscription) => {
    const status = subscription.status;
    const badgeClasses = {
      active: 'bg-green-100 text-green-800',
      trial: 'bg-blue-100 text-blue-800',
      cancelled: 'bg-red-100 text-red-800',
      expired: 'bg-gray-100 text-gray-800',
      grace_period: 'bg-yellow-100 text-yellow-800'
    };

    return (
      <span className={`px-2 py-1 text-xs font-medium rounded-full ${badgeClasses[status] || 'bg-gray-100 text-gray-800'}`}>
        {status.replace('_', ' ').toUpperCase()}
      </span>
    );
  };

  if (loading) return <div className="flex justify-center p-8"><div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>;
  if (error) return <div className="text-red-600 p-4">{error}</div>;

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">My Subscriptions</h1>

      <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        {subscriptions.map((subscription) => (
          <div key={subscription.id} className="bg-white rounded-lg shadow-md p-6">
            <div className="flex justify-between items-start mb-4">
              <h3 className="text-lg font-semibold">{subscription.tier?.name}</h3>
              {getStatusBadge(subscription)}
            </div>

            <div className="space-y-2 text-sm text-gray-600">
              <p><strong>Price:</strong> ${subscription.subscription_price}</p>
              {subscription.discounted_price && (
                <p><strong>Discounted Price:</strong> ${subscription.discounted_price}</p>
              )}
              <p><strong>Expires:</strong> {subscription.expires_at ? new Date(subscription.expires_at).toLocaleDateString() : 'Never'}</p>
              {subscription.trial_end_date && (
                <p><strong>Trial Ends:</strong> {new Date(subscription.trial_end_date).toLocaleDateString()}</p>
              )}
              <p><strong>Auto Renewal:</strong> {subscription.auto_renewal ? 'Yes' : 'No'}</p>
            </div>

            <div className="mt-4 flex space-x-2">
              <button
                onClick={() => handleCancel(subscription.id)}
                className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                disabled={!subscription.is_active}
              >
                Cancel
              </button>
              <button
                onClick={() => handleReactivate(subscription.id)}
                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                disabled={subscription.is_active}
              >
                Reactivate
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default SubscriptionList;
```

### 2. Subscription Creation Component

```jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

const CreateSubscription = () => {
  const [tiers, setTiers] = useState([]);
  const [coupons, setCoupons] = useState([]);
  const [formData, setFormData] = useState({
    tier_id: '',
    coupon_code: '',
    auto_renewal: true,
    subscription_price: ''
  });
  const [loading, setLoading] = useState(false);
  const [couponValid, setCouponValid] = useState(null);

  useEffect(() => {
    fetchTiersAndCoupons();
  }, []);

  const fetchTiersAndCoupons = async () => {
    try {
      const [tiersRes, couponsRes] = await Promise.all([
        axios.get('/api/tiers'),
        axios.get('/api/coupons')
      ]);

      setTiers(tiersRes.data.data.tiers);
      setCoupons(couponsRes.data.data.coupons);
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };

  const validateCoupon = async () => {
    if (!formData.coupon_code || !formData.tier_id) return;

    try {
      const response = await axios.post('/api/coupons/validate', {
        code: formData.coupon_code,
        tier_id: formData.tier_id,
        amount: formData.subscription_price
      });

      setCouponValid(response.data.data);
    } catch (error) {
      setCouponValid(null);
      console.error('Coupon validation failed:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await axios.post('/api/subscriptions', formData, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });

      alert('Subscription created successfully!');
      // Redirect or update UI
    } catch (error) {
      console.error('Error creating subscription:', error);
      alert('Failed to create subscription');
    } finally {
      setLoading(false);
    }
  };

  const calculateFinalPrice = () => {
    const basePrice = parseFloat(formData.subscription_price) || 0;
    if (couponValid && couponValid.is_valid) {
      return couponValid.final_amount;
    }
    return basePrice;
  };

  return (
    <div className="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
      <h2 className="text-xl font-bold mb-4">Create Subscription</h2>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Select Tier</label>
          <select
            value={formData.tier_id}
            onChange={(e) => setFormData({...formData, tier_id: e.target.value})}
            className="w-full p-2 border rounded"
            required
          >
            <option value="">Choose a tier</option>
            {tiers.map((tier) => (
              <option key={tier.id} value={tier.id}>
                {tier.name} - ${tier.price}/{tier.billing_duration}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">Subscription Price</label>
          <input
            type="number"
            step="0.01"
            value={formData.subscription_price}
            onChange={(e) => setFormData({...formData, subscription_price: e.target.value})}
            className="w-full p-2 border rounded"
            required
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-1">Coupon Code (Optional)</label>
          <div className="flex space-x-2">
            <input
              type="text"
              value={formData.coupon_code}
              onChange={(e) => setFormData({...formData, coupon_code: e.target.value})}
              className="flex-1 p-2 border rounded"
              placeholder="Enter coupon code"
            />
            <button
              type="button"
              onClick={validateCoupon}
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Validate
            </button>
          </div>
          {couponValid && (
            <div className={`mt-2 p-2 rounded ${couponValid.is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
              {couponValid.is_valid ? (
                <div>
                  <p>Discount: ${couponValid.discount}</p>
                  <p>Final Price: ${couponValid.final_amount}</p>
                </div>
              ) : (
                <p>Invalid coupon</p>
              )}
            </div>
          )}
        </div>

        <div>
          <label className="flex items-center">
            <input
              type="checkbox"
              checked={formData.auto_renewal}
              onChange={(e) => setFormData({...formData, auto_renewal: e.target.checked})}
              className="mr-2"
            />
            Enable Auto Renewal
          </label>
        </div>

        <div className="bg-gray-50 p-4 rounded">
          <h3 className="font-medium mb-2">Order Summary</h3>
          <p>Base Price: ${formData.subscription_price}</p>
          {couponValid?.is_valid && (
            <p>Discount: -${couponValid.discount}</p>
          )}
          <p className="font-bold">Final Price: ${calculateFinalPrice()}</p>
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {loading ? 'Creating...' : 'Create Subscription'}
        </button>
      </form>
    </div>
  );
};

export default CreateSubscription;
```

### 3. Subscription Management Component

```jsx
import React, { useState } from 'react';
import axios from 'axios';

const SubscriptionManager = ({ subscription, onUpdate }) => {
  const [loading, setLoading] = useState(false);

  const handleCancel = async () => {
    if (!confirm('Are you sure you want to cancel this subscription?')) return;

    setLoading(true);
    try {
      await axios.delete(`/api/subscriptions/${subscription.id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      onUpdate();
      alert('Subscription cancelled successfully');
    } catch (error) {
      console.error('Error cancelling subscription:', error);
      alert('Failed to cancel subscription');
    } finally {
      setLoading(false);
    }
  };

  const handleReactivate = async () => {
    setLoading(true);
    try {
      await axios.post(`/api/subscriptions/${subscription.id}/reactivate`, {}, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      onUpdate();
      alert('Subscription reactivated successfully');
    } catch (error) {
      console.error('Error reactivating subscription:', error);
      alert('Failed to reactivate subscription');
    } finally {
      setLoading(false);
    }
  };

  const handleExtendTrial = async (days) => {
    setLoading(true);
    try {
      await axios.post(`/api/subscriptions/${subscription.id}/extend-trial`, { days }, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      onUpdate();
      alert(`Trial extended by ${days} days`);
    } catch (error) {
      console.error('Error extending trial:', error);
      alert('Failed to extend trial');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h3 className="text-lg font-semibold mb-4">Manage Subscription</h3>

      <div className="space-y-3">
        {subscription.is_active ? (
          <button
            onClick={handleCancel}
            disabled={loading}
            className="w-full bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700 disabled:opacity-50"
          >
            {loading ? 'Cancelling...' : 'Cancel Subscription'}
          </button>
        ) : (
          <button
            onClick={handleReactivate}
            disabled={loading}
            className="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 disabled:opacity-50"
          >
            {loading ? 'Reactivating...' : 'Reactivate Subscription'}
          </button>
        )}

        {subscription.trial_end_date && new Date(subscription.trial_end_date) > new Date() && (
          <div>
            <label className="block text-sm font-medium mb-2">Extend Trial (Days)</label>
            <div className="flex space-x-2">
              <input
                type="number"
                min="1"
                max="30"
                defaultValue="7"
                id="extendDays"
                className="flex-1 p-2 border rounded"
              />
              <button
                onClick={() => handleExtendTrial(parseInt(document.getElementById('extendDays').value))}
                disabled={loading}
                className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
              >
                Extend
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default SubscriptionManager;
```

## Vue.js Integration Examples

### 1. Subscription List Component

```vue
<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">My Subscriptions</h1>

    <div v-if="loading" class="flex justify-center p-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="error" class="text-red-600 p-4">
      {{ error }}
    </div>

    <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="subscription in subscriptions"
        :key="subscription.id"
        class="bg-white rounded-lg shadow-md p-6"
      >
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-lg font-semibold">{{ subscription.tier?.name }}</h3>
          <span
            :class="getStatusBadgeClass(subscription.status)"
            class="px-2 py-1 text-xs font-medium rounded-full"
          >
            {{ formatStatus(subscription.status) }}
          </span>
        </div>

        <div class="space-y-2 text-sm text-gray-600">
          <p><strong>Price:</strong> ${{ subscription.subscription_price }}</p>
          <p v-if="subscription.discounted_price">
            <strong>Discounted Price:</strong> ${{ subscription.discounted_price }}
          </p>
          <p><strong>Expires:</strong> {{ formatDate(subscription.expires_at) }}</p>
          <p v-if="subscription.trial_end_date">
            <strong>Trial Ends:</strong> {{ formatDate(subscription.trial_end_date) }}
          </p>
          <p><strong>Auto Renewal:</strong> {{ subscription.auto_renewal ? 'Yes' : 'No' }}</p>
        </div>

        <div class="mt-4 flex space-x-2">
          <button
            @click="cancelSubscription(subscription.id)"
            :disabled="!subscription.is_active || loading"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            @click="reactivateSubscription(subscription.id)"
            :disabled="subscription.is_active || loading"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50"
          >
            Reactivate
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SubscriptionList',
  data() {
    return {
      subscriptions: [],
      loading: true,
      error: null
    }
  },
  async mounted() {
    await this.fetchSubscriptions();
  },
  methods: {
    async fetchSubscriptions() {
      try {
        const response = await this.$axios.get('/api/subscriptions', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.subscriptions = response.data.data.subscriptions;
      } catch (error) {
        this.error = 'Failed to fetch subscriptions';
        console.error('Error fetching subscriptions:', error);
      } finally {
        this.loading = false;
      }
    },

    getStatusBadgeClass(status) {
      const classes = {
        active: 'bg-green-100 text-green-800',
        trial: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
        expired: 'bg-gray-100 text-gray-800',
        grace_period: 'bg-yellow-100 text-yellow-800'
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },

    formatStatus(status) {
      return status.replace('_', ' ').toUpperCase();
    },

    formatDate(dateString) {
      if (!dateString) return 'Never';
      return new Date(dateString).toLocaleDateString();
    },

    async cancelSubscription(id) {
      if (!confirm('Are you sure you want to cancel this subscription?')) return;

      try {
        await this.$axios.delete(`/api/subscriptions/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        await this.fetchSubscriptions();
        this.$toast.success('Subscription cancelled successfully');
      } catch (error) {
        console.error('Error cancelling subscription:', error);
        this.$toast.error('Failed to cancel subscription');
      }
    },

    async reactivateSubscription(id) {
      try {
        await this.$axios.post(`/api/subscriptions/${id}/reactivate`, {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        await this.fetchSubscriptions();
        this.$toast.success('Subscription reactivated successfully');
      } catch (error) {
        console.error('Error reactivating subscription:', error);
        this.$toast.error('Failed to reactivate subscription');
      }
    }
  }
}
</script>
```

## Subscription Status Management

### Status Types

```javascript
const SUBSCRIPTION_STATUSES = {
  TRIAL: 'trial',
  ACTIVE: 'active',
  CANCELLED: 'cancelled',
  EXPIRED: 'expired',
  GRACE_PERIOD: 'grace_period'
};
```

### Status Display Logic

```javascript
const getSubscriptionStatusDisplay = (subscription) => {
  const now = new Date();
  const trialEnd = subscription.trial_end_date ? new Date(subscription.trial_end_date) : null;
  const expiresAt = subscription.expires_at ? new Date(subscription.expires_at) : null;

  if (trialEnd && now < trialEnd) {
    return {
      status: SUBSCRIPTION_STATUSES.TRIAL,
      label: 'Trial',
      color: 'blue',
      description: `Trial ends on ${trialEnd.toLocaleDateString()}`
    };
  }

  if (!subscription.is_active) {
    return {
      status: SUBSCRIPTION_STATUSES.CANCELLED,
      label: 'Cancelled',
      color: 'red',
      description: 'Subscription has been cancelled'
    };
  }

  if (expiresAt && now > expiresAt) {
    // Check grace period
    const graceEnd = new Date(expiresAt);
    graceEnd.setDate(graceEnd.getDate() + (subscription.grace_period_days || 0));

    if (now <= graceEnd) {
      return {
        status: SUBSCRIPTION_STATUSES.GRACE_PERIOD,
        label: 'Grace Period',
        color: 'yellow',
        description: `Expires on ${expiresAt.toLocaleDateString()}`
      };
    } else {
      return {
        status: SUBSCRIPTION_STATUSES.EXPIRED,
        label: 'Expired',
        color: 'gray',
        description: 'Subscription has expired'
      };
    }
  }

  return {
    status: SUBSCRIPTION_STATUSES.ACTIVE,
    label: 'Active',
    color: 'green',
    description: expiresAt ? `Renews on ${expiresAt.toLocaleDateString()}` : 'Active subscription'
  };
};
```

### Status-Based Actions

```javascript
const getAvailableActions = (subscription) => {
  const status = getSubscriptionStatusDisplay(subscription);

  switch (status.status) {
    case SUBSCRIPTION_STATUSES.TRIAL:
      return ['cancel', 'extend_trial', 'upgrade'];

    case SUBSCRIPTION_STATUSES.ACTIVE:
      return ['cancel', 'upgrade', 'change_billing'];

    case SUBSCRIPTION_STATUSES.CANCELLED:
      return ['reactivate'];

    case SUBSCRIPTION_STATUSES.GRACE_PERIOD:
      return ['reactivate', 'pay_now'];

    case SUBSCRIPTION_STATUSES.EXPIRED:
      return ['reactivate', 'resubscribe'];

    default:
      return [];
  }
};
```

## Billing Integration

### Payment Processing Setup

```javascript
// Stripe Integration Example
import { loadStripe } from '@stripe/stripe-js';

const stripePromise = loadStripe(process.env.REACT_APP_STRIPE_PUBLISHABLE_KEY);

const processPayment = async (subscriptionData) => {
  try {
    // Create payment intent on backend
    const response = await axios.post('/api/create-payment-intent', {
      subscription_price: subscriptionData.subscription_price,
      tier_id: subscriptionData.tier_id,
      coupon_code: subscriptionData.coupon_code
    });

    const { clientSecret } = response.data;

    // Confirm payment with Stripe
    const stripe = await stripePromise;
    const { error } = await stripe.confirmCardPayment(clientSecret, {
      payment_method: {
        card: elements.getElement('card'),
        billing_details: {
          name: subscriptionData.billing_name,
          email: subscriptionData.billing_email
        }
      }
    });

    if (error) {
      throw new Error(error.message);
    }

    // Create subscription after successful payment
    await createSubscription(subscriptionData);

    return { success: true };
  } catch (error) {
    console.error('Payment failed:', error);
    return { success: false, error: error.message };
  }
};
```

### Billing Cycle Management

```javascript
const calculateNextBillingDate = (tier, startDate = new Date()) => {
  const billingDuration = tier.billing_duration;

  switch (billingDuration) {
    case 'day':
      return new Date(startDate.getTime() + 24 * 60 * 60 * 1000);
    case 'week':
      return new Date(startDate.getTime() + 7 * 24 * 60 * 60 * 1000);
    case 'month':
      const nextMonth = new Date(startDate);
      nextMonth.setMonth(nextMonth.getMonth() + 1);
      return nextMonth;
    case '6 months':
      const next6Months = new Date(startDate);
      next6Months.setMonth(next6Months.getMonth() + 6);
      return next6Months;
    case 'year':
      const nextYear = new Date(startDate);
      nextYear.setFullYear(nextYear.getFullYear() + 1);
      return nextYear;
    default:
      return new Date(startDate.getTime() + 30 * 24 * 60 * 60 * 1000); // Default to monthly
  }
};

const formatBillingCycle = (billingDuration) => {
  const formats = {
    'day': 'Daily',
    'week': 'Weekly',
    'month': 'Monthly',
    '6 months': 'Every 6 Months',
    'year': 'Yearly'
  };

  return formats[billingDuration] || 'Monthly';
};
```

### Prorated Billing Calculation

```javascript
const calculateProratedAmount = (currentPlan, newPlan, daysRemaining) => {
  const currentDailyRate = currentPlan.price / getDaysInBillingCycle(currentPlan.billing_duration);
  const newDailyRate = newPlan.price / getDaysInBillingCycle(newPlan.billing_duration);

  const currentPlanCredit = currentDailyRate * daysRemaining;
  const newPlanCharge = newDailyRate * daysRemaining;

  return Math.max(0, newPlanCharge - currentPlanCredit);
};

const getDaysInBillingCycle = (billingDuration) => {
  const days = {
    'day': 1,
    'week': 7,
    'month': 30, // Approximate
    '6 months': 180,
    'year': 365
  };

  return days[billingDuration] || 30;
};
```

## Webhook Handling

### Setting up Webhooks

```javascript
// webhookHandler.js
const handleSubscriptionWebhook = async (webhookData) => {
  const { type, data } = webhookData;

  switch (type) {
    case 'subscription.created':
      await handleSubscriptionCreated(data);
      break;

    case 'subscription.updated':
      await handleSubscriptionUpdated(data);
      break;

    case 'subscription.cancelled':
      await handleSubscriptionCancelled(data);
      break;

    case 'subscription.payment_succeeded':
      await handlePaymentSucceeded(data);
      break;

    case 'subscription.payment_failed':
      await handlePaymentFailed(data);
      break;

    case 'subscription.trial_will_end':
      await handleTrialWillEnd(data);
      break;

    default:
      console.log('Unhandled webhook type:', type);
  }
};

const handleSubscriptionCreated = async (data) => {
  try {
    // Update local subscription state
    await updateLocalSubscription(data.subscription);

    // Send notification to user
    await sendNotification({
      type: 'subscription_created',
      user_id: data.subscription.user_id,
      message: `Your ${data.subscription.tier.name} subscription has been activated!`
    });

    // Update analytics
    await trackEvent('subscription_created', {
      tier: data.subscription.tier.name,
      price: data.subscription.subscription_price
    });
  } catch (error) {
    console.error('Error handling subscription created:', error);
  }
};

const handlePaymentSucceeded = async (data) => {
  try {
    // Update subscription status
    await updateSubscriptionStatus(data.subscription.id, 'active');

    // Send confirmation email
    await sendPaymentConfirmationEmail(data);

    // Update billing history
    await addBillingRecord({
      subscription_id: data.subscription.id,
      amount: data.amount,
      status: 'paid',
      payment_date: new Date()
    });
  } catch (error) {
    console.error('Error handling payment succeeded:', error);
  }
};

const handlePaymentFailed = async (data) => {
  try {
    // Update subscription status
    await updateSubscriptionStatus(data.subscription.id, 'payment_failed');

    // Send payment failure notification
    await sendNotification({
      type: 'payment_failed',
      user_id: data.subscription.user_id,
      message: 'Your payment failed. Please update your payment method.'
    });

    // Schedule retry or dunning management
    await schedulePaymentRetry(data.subscription.id);
  } catch (error) {
    console.error('Error handling payment failed:', error);
  }
};
```

### Webhook Security

```javascript
const verifyWebhookSignature = (payload, signature, secret) => {
  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');

  return crypto.timingSafeEqual(
    Buffer.from(signature, 'hex'),
    Buffer.from(expectedSignature, 'hex')
  );
};

// Express middleware for webhook verification
const webhookMiddleware = (req, res, next) => {
  const signature = req.headers['x-webhook-signature'];
  const secret = process.env.WEBHOOK_SECRET;

  if (!signature) {
    return res.status(400).json({ error: 'Missing webhook signature' });
  }

  const isValid = verifyWebhookSignature(
    JSON.stringify(req.body),
    signature,
    secret
  );

  if (!isValid) {
    return res.status(400).json({ error: 'Invalid webhook signature' });
  }

  next();
};
```

## Error Handling & Troubleshooting

### Common Error Scenarios

```javascript
const ERROR_TYPES = {
  PAYMENT_FAILED: 'payment_failed',
  SUBSCRIPTION_EXPIRED: 'subscription_expired',
  COUPON_INVALID: 'coupon_invalid',
  TIER_UNAVAILABLE: 'tier_unavailable',
  NETWORK_ERROR: 'network_error',
  VALIDATION_ERROR: 'validation_error'
};

const handleApiError = (error) => {
  if (!error.response) {
    // Network error
    return {
      type: ERROR_TYPES.NETWORK_ERROR,
      message: 'Network connection failed. Please check your internet connection.',
      action: 'retry'
    };
  }

  const { status, data } = error.response;

  switch (status) {
    case 400:
      return handleValidationError(data);
    case 401:
      return {
        type: 'authentication_error',
        message: 'Your session has expired. Please log in again.',
        action: 'redirect_to_login'
      };
    case 403:
      return {
        type: 'permission_error',
        message: 'You do not have permission to perform this action.',
        action: 'show_error'
      };
    case 404:
      return {
        type: 'not_found',
        message: 'The requested resource was not found.',
        action: 'show_error'
      };
    case 422:
      return handleValidationError(data);
    case 500:
      return {
        type: 'server_error',
        message: 'A server error occurred. Please try again later.',
        action: 'retry'
      };
    default:
      return {
        type: 'unknown_error',
        message: 'An unexpected error occurred.',
        action: 'show_error'
      };
  }
};

const handleValidationError = (data) => {
  const errors = data.errors || {};
  const firstError = Object.values(errors)[0];

  return {
    type: ERROR_TYPES.VALIDATION_ERROR,
    message: Array.isArray(firstError) ? firstError[0] : firstError,
    errors: errors,
    action: 'show_validation_errors'
  };
};
```

### Error Recovery Strategies

```javascript
const retryWithBackoff = async (fn, maxRetries = 3, baseDelay = 1000) => {
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      return await fn();
    } catch (error) {
      if (attempt === maxRetries) {
        throw error;
      }

      const delay = baseDelay * Math.pow(2, attempt - 1);
      await new Promise(resolve => setTimeout(resolve, delay));
    }
  }
};

// Usage example
const createSubscriptionWithRetry = async (subscriptionData) => {
  return retryWithBackoff(async () => {
    return await axios.post('/api/subscriptions', subscriptionData, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
  });
};
```

### Troubleshooting Guide

#### Issue: Subscription not activating after payment

**Possible Causes:**
1. Webhook not received
2. Payment processing delay
3. Database transaction failure

**Solutions:**
```javascript
const checkSubscriptionStatus = async (subscriptionId) => {
  try {
    const response = await axios.get(`/api/subscriptions/${subscriptionId}`);
    const subscription = response.data.data.subscription;

    if (subscription.status === 'active') {
      // Subscription is active
      return { status: 'active', subscription };
    } else {
      // Check payment status
      const paymentStatus = await checkPaymentStatus(subscriptionId);
      return { status: 'pending', paymentStatus };
    }
  } catch (error) {
    console.error('Error checking subscription status:', error);
    return { status: 'error', error };
  }
};
```

#### Issue: Coupon not applying correctly

**Debug Steps:**
```javascript
const debugCouponApplication = async (couponCode, tierId, amount) => {
  try {
    // Step 1: Validate coupon exists
    const couponResponse = await axios.get(`/api/coupons?code=${couponCode}`);
    const coupon = couponResponse.data.data.coupons[0];

    if (!coupon) {
      return { error: 'Coupon not found' };
    }

    // Step 2: Check coupon validity
    if (!coupon.is_active) {
      return { error: 'Coupon is not active' };
    }

    // Step 3: Check expiry
    if (coupon.expires_at && new Date(coupon.expires_at) < new Date()) {
      return { error: 'Coupon has expired' };
    }

    // Step 4: Check usage limits
    if (coupon.usage_limit && coupon.usage_count >= coupon.usage_limit) {
      return { error: 'Coupon usage limit exceeded' };
    }

    // Step 5: Check tier applicability
    if (coupon.applicable_tiers && !coupon.applicable_tiers.includes(tierId)) {
      return { error: 'Coupon not applicable to this tier' };
    }

    // Step 6: Check minimum amount
    if (coupon.minimum_amount && amount < coupon.minimum_amount) {
      return { error: `Minimum purchase amount is $${coupon.minimum_amount}` };
    }

    // Step 7: Calculate discount
    const discount = coupon.calculateDiscount(amount);
    const finalAmount = Math.max(0, amount - discount);

    return {
      valid: true,
      coupon,
      discount,
      finalAmount
    };

  } catch (error) {
    console.error('Error debugging coupon:', error);
    return { error: 'Failed to validate coupon' };
  }
};
```

#### Issue: Real-time updates not working

**Solutions:**
1. Check WebSocket connection
2. Verify event broadcasting setup
3. Check client-side event listeners

```javascript
// WebSocket connection for real-time updates
class SubscriptionWebSocket {
  constructor(userId) {
    this.userId = userId;
    this.socket = null;
    this.listeners = {};
  }

  connect() {
    this.socket = new WebSocket(`ws://your-domain.com/subscriptions?user_id=${this.userId}`);

    this.socket.onopen = () => {
      console.log('WebSocket connected');
    };

    this.socket.onmessage = (event) => {
      const data = JSON.parse(event.data);
      this.handleMessage(data);
    };

    this.socket.onclose = () => {
      console.log('WebSocket disconnected');
      // Implement reconnection logic
      setTimeout(() => this.connect(), 5000);
    };

    this.socket.onerror = (error) => {
      console.error('WebSocket error:', error);
    };
  }

  handleMessage(data) {
    const { type, subscription } = data;

    switch (type) {
      case 'subscription_updated':
        this.emit('subscriptionUpdated', subscription);
        break;
      case 'payment_failed':
        this.emit('paymentFailed', subscription);
        break;
      case 'trial_ending':
        this.emit('trialEnding', subscription);
        break;
    }
  }

  on(event, callback) {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    this.listeners[event].push(callback);
  }

  emit(event, data) {
    if (this.listeners[event]) {
      this.listeners[event].forEach(callback => callback(data));
    }
  }

  disconnect() {
    if (this.socket) {
      this.socket.close();
