# Frisbii Pay for Hyvä Checkout - Compatibility Module

[Frisbii Pay for Magento 2](https://github.com/RadarsofthouseDev/frisbii-pay-for-magento2) compatibility for [Hyvä Checkout](https://www.hyva.io/hyva-checkout.html).

This module enables all Frisbii Pay payment methods (Credit card, MobilePay, Vipps, Klarna, Apple Pay, Google Pay, PayPal, etc.) to work seamlessly with Hyvä's checkout system. It provides Magewire payment components and proper redirect handling for Hyvä's checkout flow.

## How It Works

- **Redirect Flow**: After order placement with Frisbii payment methods, customers are redirected to Frisbii's secure payment gateway, or the Frisbii payment form is rendered for Overlay and Embedded modes.

## Requirements

- **Magento 2.4.x** (compatible with Hyvä Checkout requirements)
- **Hyvä Checkout** (`hyva-themes/magento2-hyva-checkout ^1.0`)
- **Frisbii Pay Base Module**: Either
  - `radarsofthouse/reepay` ^1.2.67 (GitHub version) OR
  - `reepay-payment/reepay` ^1.2.67 (Adobe Marketplace version)

## Installation

```bash
composer require radarsofthouse/magento2-hyva-checkout-frisbii-pay
bin/magento setup:upgrade
bin/magento cache:flush
```

## Configuration

Configure individual payment methods in the Frisbii Pay Base Module: `Stores > Configuration > Sales > Payment Methods > Frisbii Pay`

## Troubleshooting

### Payment methods not appearing in Hyvä Checkout
- Verify the base [Frisbii Pay module](https://github.com/RadarsofthouseDev/frisbii-pay-for-magento2) is installed and enabled
- Clear Magento cache: `bin/magento cache:flush`

## Support
- You can create issues on our repository. In case of specific problems with your account, please contact support@radarsofthouse.dk
