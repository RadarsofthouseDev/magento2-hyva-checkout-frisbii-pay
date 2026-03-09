<?php

namespace Radarsofthouse\HyvaCheckoutFrisbii\Observer\HyvaConfigGenerateBefore;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar
    ) {}

    public function execute(Observer $observer): void
    {
        $config = $observer->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];

        $moduleName = 'Radarsofthouse_HyvaCheckoutFrisbii';
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);

        if ($path) {
            $extensions[] = $path;
            $config->setData('extensions', $extensions);
        }
    }
}
