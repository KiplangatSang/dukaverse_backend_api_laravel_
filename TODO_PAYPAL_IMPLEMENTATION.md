# PayPal Payment Gateway Implementation

## Overview
Implement PayPal as a payment gateway following the existing PaymentGateways structure (IpayData abstract class, B2B/C2B/Billing subdirectories) and existing helpers (ResponseHelper, etc.). This follows the pattern of iPay integration for consistency.

## Completed Steps
- [x] Install PayPal PHP SDK (`composer require paypal/paypal-checkout-sdk`)
- [x] Add PayPal configuration to `config/services.php`
- [x] Create `app/PaymentGateways/PayPal/PayPalPayments.php` extending IpayData
- [x] Add PayPal methods to `PaymentService.php`
- [x] Add PayPal endpoints to `PaymentController.php`
- [x] Add PayPal routes to `routes/api.php`

## File Structure
```
app/PaymentGateways/
├── IpayData.php (abstract)
├── B2BPayments/
│   └── IpayPayments.php
├── C2BPayments/
├── Billing/
└── PayPal/
    └── PayPalPayments.php (new)
```

## Configuration
Add to `.env`:
```
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox  # or live
```

## API Endpoints
- `POST /api/v1/payments/paypal` - Create PayPal order
- `POST /api/v1/payments/paypal/capture` - Capture PayPal payment

## Usage
1. Create an order with amount, currency, description
2. Redirect user to PayPal approval URL
3. After approval, capture the payment using order ID

## Testing
- Test with PayPal sandbox environment
- Verify order creation and capture flows
- Check error handling for invalid credentials or amounts

## Next Steps
- [ ] Test PayPal integration with sandbox
- [ ] Implement PayPal callbacks/webhooks if needed
- [ ] Add PayPal to available gateways list in PaymentController
- [ ] Update frontend to support PayPal payments
- [ ] Add PayPal to PaymentGateway model constants if needed
