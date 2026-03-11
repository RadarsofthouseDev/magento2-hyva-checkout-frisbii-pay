# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.1] - 2026-03-11

### Fixed

- Corrected extension path structure in Hyvä config registration (`RegisterModuleForHyvaConfig`).

---

## [1.0.0] - 2026-03-09

### Added

- Initial release — Hyvä Checkout compatibility for all 40+ Frisbii Pay payment methods.
- Single `ReepayPlaceOrderService` (Magewire) handling redirect to `reepay/standard/redirect` for every method.
- Shared `PaymentConfig` ViewModel: payment icons (with merchant override), saved credit cards, AJAX endpoint URLs.
- CSP-safe Alpine.js `reepayCardSelector` component loaded via `magewire.plugin.scripts`.
- Layout, DI, and observer wiring for Tailwind CSS purge registration.

---

[1.0.1]: https://github.com/RadarsofthouseDev/magento2-hyva-checkout-frisbii-pay/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/RadarsofthouseDev/magento2-hyva-checkout-frisbii-pay/releases/tag/1.0.0
