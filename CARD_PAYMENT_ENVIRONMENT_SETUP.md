# Card Payment Environment Setup Guide

## Overview

This guide provides step-by-step instructions for setting up Stripe card payments in different environments (development, staging, production).

## Prerequisites

1. **Stripe Account**: Create a Stripe account at https://stripe.com
2. **Business Verification**: Complete Stripe's business verification process
3. **Bank Account**: Connect your bank account for payouts
4. **SSL Certificate**: Required for production (HTTPS)

## Stripe Account Setup

### 1. Create Stripe Account

1. Go to https://stripe.com and click "Sign up"
2. Enter your email and create a password
3. Verify your email address
4. Complete your business profile

### 2. Business Verification

1. In Stripe Dashboard, go to Settings → Business details
2. Fill in all required business information
3. Upload required documents (ID, business license, etc.)
4. Submit for verification (usually takes 24-48 hours)

### 3. Bank Account Setup

1. Go to Settings → Bank accounts
2. Add your bank account details
3. Verify the account (micro-deposits or instant verification)

## API Keys Configuration

### Development Environment

#### 1. Get Test API Keys

1. In Stripe Dashboard, go to Developers → API keys
2. Copy the **Publishable key** (starts with `pk_test_`)
3. Copy the **Secret key** (starts with `sk_test_`)

#### 2. Environment Variables (.env)

```env
# Stripe Configuration - Development
STRIPE_PUBLISHABLE_KEY=pk_test_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_SECRET_KEY= <your-stripe-secret-key>
STRIPE_WEBHOOK_SECRET=  <your-stripe-secret-key>
STRIPE_WEBHOOK_URL=http://localhost:8000/api/v1/payments/stripe/webhook

# Application Settings
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Staging Environment

#### 1. Get Test API Keys (Same as Development)

Use the same test keys for staging to avoid real charges during testing.

#### 2. Environment Variables (.env.staging)

```env
Stripe Configuration - Staging
STRIPE_PUBLISHABLE_KEY=pk_test_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_SECRET_KEY= <your-stripe-secret-key>
STRIPE_WEBHOOK_SECRET= <your-stripe-secret-key>
STRIPE_WEBHOOK_URL=https://staging.your-domain.com/api/v1/payments/stripe/webhook

# Application Settings
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.your-domain.com
```

### Production Environment

#### 1. Get Live API Keys

⚠️ **Important**: Live keys process real payments. Keep them secure!

1. In Stripe Dashboard, toggle "Test mode" to OFF
2. Copy the **Publishable key** (starts with `pk_live_`)
3. Copy the **Secret key** (starts with `sk_live_`)

#### 2. Environment Variables (.env.production)

```env
# Stripe Configuration - Production
STRIPE_PUBLISHABLE_KEY=pk_live_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
STRIPE_SECRET_KEY= <your-stripe-secret-key>
STRIPE_WEBHOOK_SECRET= <your-stripe-secret-key>
STRIPE_WEBHOOK_URL=https://your-domain.com/api/v1/payments/stripe/webhook

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

## Webhook Configuration

### 1. Create Webhook Endpoint

1. In Stripe Dashboard, go to Developers → Webhooks
2. Click "Add endpoint"
3. Enter your webhook URL:
   - Development: `http://localhost:8000/api/v1/payments/stripe/webhook`
   - Production: `https://your-domain.com/api/v1/payments/stripe/webhook`

### 2. Select Events

Subscribe to these events:
- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `payment_intent.canceled`
- `charge.succeeded`
- `charge.failed`

### 3. Get Webhook Secret

1. After creating the webhook, copy the **Signing secret**
2. Add it to your environment variables as `STRIPE_WEBHOOK_SECRET`

## Laravel Configuration

### 1. Install Stripe PHP SDK

```bash
composer require stripe/stripe-php
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Stripe\StripeServiceProvider"
```

### 3. Config File (config/services.php)

```php
<?php

return [
    // ... other services

    'stripe' => [
        'model' => env('STRIPE_MODEL', 'User'),
        'key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'url' => env('STRIPE_WEBHOOK_URL'),
        ],
    ],
];
```

## Frontend Configuration

### 1. Environment Variables

#### For React/Vue/Angular (.env)

```env
# Development
REACT_APP_STRIPE_PUBLISHABLE_KEY=pk_test_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

# Production
REACT_APP_STRIPE_PUBLISHABLE_KEY=pk_live_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

#### For Vanilla JavaScript

```javascript
// config.js
const config = {
  stripe: {
    publishableKey: process.env.NODE_ENV === 'production'
      ? 'pk_live_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
      : 'pk_test_51HXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'
  }
};
```

### 2. Load Stripe.js

#### HTML
```html
<script src="https://js.stripe.com/v3/"></script>
```

#### React
```javascript
import { loadStripe } from '@stripe/stripe-js';

const stripePromise = loadStripe(process.env.REACT_APP_STRIPE_PUBLISHABLE_KEY);
```

## Testing Setup

### 1. Test Mode Verification

Ensure you're using test keys by checking the Stripe Dashboard:
- Test mode should show "TEST DATA" in the top right
- All transactions should be free

### 2. Test Cards

Use these test card numbers:

```javascript
const testCards = {
  success: '4242424242424242',
  decline: '4000000000000002',
  insufficient: '4000000000009995',
  expired: '4000000000000069'
};
```

### 3. Test Webhooks

1. Install Stripe CLI: https://stripe.com/docs/stripe-cli
2. Login: `stripe login`
3. Forward webhooks: `stripe listen --forward-to localhost:8000/api/v1/payments/stripe/webhook`

## Production Deployment Checklist

### Pre-Launch

- [ ] Stripe account fully verified
- [ ] Bank account connected and verified
- [ ] Live API keys configured
- [ ] Webhook endpoint configured with live URL
- [ ] SSL certificate installed
- [ ] Domain configured for HTTPS
- [ ] Environment variables updated
- [ ] Frontend built with production keys

### Security Checks

- [ ] API keys not committed to version control
- [ ] Environment variables properly secured
- [ ] Webhook secret properly configured
- [ ] PCI compliance requirements met (handled by Stripe)

### Testing in Production

- [ ] Test small payment ($1.00) to verify everything works
- [ ] Verify webhook events are received
- [ ] Check transaction logs in Stripe Dashboard
- [ ] Test refund functionality
- [ ] Verify email receipts are sent

## Monitoring and Alerts

### Stripe Dashboard Monitoring

1. **Balance**: Monitor account balance and pending payouts
2. **Transactions**: Review all payment activity
3. **Disputes**: Monitor and respond to chargebacks
4. **Radar**: Review fraud prevention alerts

### Application Monitoring

Set up monitoring for:
- Payment success rates
- Failed payment attempts
- Webhook delivery failures
- API response times

### Alerts Configuration

Configure alerts for:
- High failure rates
- Unusual transaction volumes
- Webhook failures
- Account balance thresholds

## Troubleshooting

### Common Issues

#### 1. "Invalid API Key"

**Symptoms:** All Stripe requests fail with authentication errors

**Solutions:**
- Check if you're using the correct environment (test/live)
- Verify the API key is properly set in environment variables
- Ensure no extra spaces or characters in the key

#### 2. Webhooks Not Working

**Symptoms:** Webhook events not received

**Solutions:**
- Verify webhook URL is accessible
- Check webhook secret matches
- Ensure HTTPS is used in production
- Test with Stripe CLI

#### 3. Payments Declined

**Symptoms:** Legitimate payments being declined

**Solutions:**
- Check Stripe Dashboard for declined payment details
- Review Radar rules
- Contact cardholder's bank if needed

#### 4. SSL Certificate Issues

**Symptoms:** Webhooks fail with SSL errors

**Solutions:**
- Ensure valid SSL certificate is installed
- Check certificate expiration date
- Verify certificate chain is complete

### Debug Mode

Enable debug logging in Laravel:

```php
// config/logging.php
'channels' => [
    'stripe' => [
        'driver' => 'single',
        'path' => storage_path('logs/stripe.log'),
        'level' => 'debug',
    ],
],
```

Log Stripe events:

```php
use Illuminate\Support\Facades\Log;

Log::channel('stripe')->info('Payment processed', [
    'amount' => $amount,
    'currency' => $currency,
    'transaction_id' => $transactionId
]);
```

## Support Resources

### Stripe Support
- **Dashboard:** https://dashboard.stripe.com
- **Documentation:** https://stripe.com/docs
- **Support:** https://stripe.com/contact

### Laravel Stripe Integration
- **Cashier Documentation:** https://laravel.com/docs/cashier
- **Community Packages:** https://github.com/stripe/stripe-php

### Security
- **PCI Compliance:** https://stripe.com/docs/security
- **Best Practices:** https://stripe.com/docs/security/best-practices

## Cost Structure

### Stripe Fees

- **Domestic cards:** 2.9% + 30¢ per transaction
- **International cards:** 3.4% + 30¢ per transaction
- **Currency conversion:** 1% additional fee
- **ACH payments:** 0.8% (max $5)
- **Wire transfers:** $25 per transfer

### Additional Costs

- **Monthly fee:** None for standard accounts
- **Chargeback fee:** $15 per chargeback
- **Reserve:** May be held for high-risk businesses

## Compliance

### PCI DSS
- Stripe handles PCI DSS Level 1 compliance
- No card data stored on your servers
- Tokenization ensures security

### Regional Compliance
- **GDPR:** For EU customers
- **PSD2:** For European payments
- **SOX:** For financial reporting

### Business Requirements
- Valid business registration
- Tax ID verification
- Bank account verification
- Identity verification for owners

## Going Live Checklist

### Week Before Launch
- [ ] Complete Stripe account verification
- [ ] Test all payment flows with live keys (use small amounts)
- [ ] Configure production webhooks
- [ ] Update all environment variables
- [ ] Deploy code to production
- [ ] Test production endpoints

### Day of Launch
- [ ] Monitor Stripe Dashboard for first transactions
- [ ] Verify webhook events are working
- [ ] Check application logs for errors
- [ ] Monitor payment success rates
- [ ] Be available for support during peak hours

### Post-Launch
- [ ] Monitor for 24-48 hours
- [ ] Review declined payments
- [ ] Optimize conversion rates
- [ ] Set up automated reporting

## Emergency Contacts

- **Stripe Support:** support@stripe.com
- **Your Bank:** Contact details for payout issues
- **Technical Support:** Your development team
- **Customer Support:** For payment-related customer inquiries

Remember: Always test thoroughly before going live, and have a rollback plan ready in case of issues.
