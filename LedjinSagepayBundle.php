<?php

namespace Ledjin\Bundle\SagepayBundle;

use Ledjin\Bundle\SagepayBundle\DependencyInjection\Factory\Payment\SagepayOnsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LedjinSagepayBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        /** @var  PayumExtension $payumExtension */
        $payumExtension = $container->getExtension('payum');

        $payumExtension->addPaymentFactory(new SagepayOnsitePaymentFactory);
    }
}
