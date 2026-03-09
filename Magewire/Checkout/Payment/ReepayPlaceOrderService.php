<?php

namespace Radarsofthouse\HyvaCheckoutFrisbii\Magewire\Checkout\Payment;

use Hyva\Checkout\Model\Magewire\Payment\AbstractPlaceOrderService;
use Magento\Quote\Model\Quote;

class ReepayPlaceOrderService extends AbstractPlaceOrderService
{
    /**
     * Get redirect URL
     *
     * @param Quote $quote
     * @param int|null $orderId
     * @return string
     */
    public function getRedirectUrl(Quote $quote, ?int $orderId = null): string
    {
        return 'reepay/standard/redirect';
    }
}
