<?php

declare(strict_types=1);

namespace Radarsofthouse\HyvaCheckoutFrisbii\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Radarsofthouse\Reepay\Helper\Customer as CustomerHelper;
use Radarsofthouse\Reepay\Helper\Data as ReepayHelper;
use Radarsofthouse\Reepay\Model\Config\Source\Allowwedpayment;

class PaymentConfig implements ArgumentInterface
{
    private const CUSTOM_ICON_FOLDER = "billwerk/icons/";

    private const OVERRIDABLE_PAYMENT_ICONS = [
        'forbrugsforeningen',
        'mobilepay',
        'viabill',
        'anyday',
        'klarna-pay-later',
        'klarna-pay-now',
        'klarna-slice-it',
        'klarna-direct-bank-transfer',
        'klarna-direct-debit',
        'applepay',
        'paypal',
        'vipps',
        'googlepay',
        'blik_oc',
        'giropay',
        'p24',
        'swish',
        'ideal',
        'verkkopankki',
        'sepa',
        'eps',
        'mb-way',
        'multibanco',
        'mybank',
        'payconiq',
        'paysafecard',
        'paysera',
        'postfinance',
        'satispay',
        'trustly',
        'wechatpay'
    ];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly AssetRepository $assetRepository,
        private readonly CustomerSession $customerSession,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerHelper $customerHelper,
        private readonly ReepayHelper $reepayHelper,
        private readonly Allowwedpayment $allowwedpayment
    ) {}

    /**
     * Get payment method instructions from backend configuration
     *
     * @param string $methodCode
     * @return string|null
     */
    public function getInstruction(string $methodCode)
    {
        return $this->scopeConfig->getValue(
            "payment/{$methodCode}/instructions",
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if a specific config value is enabled
     *
     * @param string $methodCode
     * @param string $field
     * @return bool
     */
    public function isConfigEnabled(string $methodCode, string $field = 'active')
    {
        return (bool) $this->scopeConfig->isSetFlag(
            "payment/{$methodCode}/{$field}",
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get any payment method config value
     *
     * @param string $methodCode
     * @param string $field
     * @return mixed
     */
    public function getConfigValue(string $methodCode, string $field)
    {
        return $this->scopeConfig->getValue(
            "payment/{$methodCode}/{$field}",
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get payment icons for reepay_payment method
     * Reuses logic from Radarsofthouse\Reepay\Block\Paymenticons
     *
     * @return array
     */
    public function getPaymentIcons()
    {
        $paymentIconsConfig = $this->getConfigValue('reepay_payment', 'payment_icons');
        $useCustomIcon = $this->getConfigValue('reepay_payment', 'use_custom_icon');

        if (empty($paymentIconsConfig)) {
            return [];
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $paymentIconsList = explode(',', $paymentIconsConfig);
        $paymentIcons = [];

        foreach ($paymentIconsList as $paymentIcon) {
            // Default icon from Reepay module
            $iconUrl = $this->assetRepository->getUrl(
                'Radarsofthouse_Reepay::img/payment_icons/' . $paymentIcon . '.png'
            );

            // Check for custom icon override
            if ($useCustomIcon && in_array($paymentIcon, self::OVERRIDABLE_PAYMENT_ICONS)) {
                $paymentMethod = 'reepay_' . str_replace("-", "", $paymentIcon);
                $customIcon = $this->getConfigValue($paymentMethod, 'custom_icon');
                if (!empty($customIcon)) {
                    $iconUrl = $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
                }
            }

            $paymentIcons[] = $iconUrl;
        }

        return $paymentIcons;
    }

    /**
     * Get single payment method icon
     * Reuses logic from Radarsofthouse\Reepay\Block\Paymenticons individual methods
     *
     * @param string $methodCode Payment method code (e.g., 'reepay_mobilepay')
     * @param string $iconFileName Icon file name without extension (e.g., 'mobilepay')
     * @return string|null
     */
    public function getPaymentMethodIcon(string $methodCode, string $iconFileName)
    {
        $showIcon = $this->getConfigValue($methodCode, 'show_icon');
        if (!$showIcon) {
            return null;
        }

        $customIcon = $this->getConfigValue($methodCode, 'custom_icon');
        if (!empty($customIcon)) {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $mediaUrl . self::CUSTOM_ICON_FOLDER . $customIcon;
        }

        // Default icon from Reepay module
        return $this->assetRepository->getUrl(
            'Radarsofthouse_Reepay::img/payment_icons/' . $iconFileName . '.png'
        );
    }

    /**
     * Get saved credit cards for the current customer
     * Reuses logic from Radarsofthouse\Reepay\Block\SavedCreditCards
     *
     * @return array
     */
    public function getSavedCreditCards()
    {
        $savedCreditCards = [];

        $storeId = $this->storeManager->getStore()->getId();
        $saveCardEnable = $this->reepayHelper->getConfig('save_card_enable', $storeId);

        if (!$saveCardEnable) {
            return [];
        }

        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        $apiKey = $this->reepayHelper->getApiKey($storeId);
        $savedCreditCards = $this->customerHelper->getPaymentCardsByCustomer(
            $apiKey,
            $this->customerSession->getCustomer()
        );

        // Check Age Verification
        $ageVerificationEnabled = (bool) $this->reepayHelper->getConfig(
            'age_verification_enabled',
            $storeId
        );

        if (!empty($savedCreditCards) && $ageVerificationEnabled) {
            $hasAgeRestrictedProduct = false;
            $quote = $this->checkoutSession->getQuote();

            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $ageAttr = $product->getCustomAttribute('frisbii_minimum_user_age');
                $minimumAge = $ageAttr ? (int) $ageAttr->getValue() : 0;

                if ($minimumAge > 0) {
                    $hasAgeRestrictedProduct = true;
                    break;
                }
            }

            // Disable saved card when Age Verification is enabled and has Age Restricted Product in cart
            if ($hasAgeRestrictedProduct) {
                return [];
            }
        }

        return $savedCreditCards;
    }

    /**
     * Get saved credit card ID from quote
     *
     * @return string|null
     */
    public function getSavedCreditCardFromQuote()
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote->getReepayCreditCard();
    }

    /**
     * Get allowed payment types
     *
     * @return array
     */
    public function getAllowedPayments()
    {
        $allowwedpayments = $this->allowwedpayment->toOptionArray();
        $_allowwedpayments = [];

        foreach ($allowwedpayments as $allowwedpayment) {
            if ($allowwedpayment['value'] == 'card') {
                $_allowwedpayments[$allowwedpayment['value']] = __('Debit/Credit card');
            } else {
                $_allowwedpayments[$allowwedpayment['value']] = $allowwedpayment['label'];
            }
        }

        return $_allowwedpayments;
    }

    /**
     * Get Remove Card URL
     *
     * @return string
     */
    public function getRemoveCardUrl()
    {
        return $this->storeManager->getStore()->getUrl('reepay/standard/removeCard');
    }

    /**
     * Get Set Credit Card URL
     *
     * @return string
     */
    public function getSetCreditCardUrl()
    {
        return $this->storeManager->getStore()->getUrl('reepay/standard/setCreditCard');
    }
}
