<?php

namespace Ledjin\Bundle\SagepayBundle;

use Ledjin\Bundle\SagepayBundle\DependencyInjection\Factory\Payment\SagepayOffsitePaymentFactory;
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
        parent::build($container);

        /** @var $extension PayumExtension */
        $extension = $container->getExtension('payum');
        $extension->addPaymentFactory(new SagepayOffsitePaymentFactory);
    }
}
