<?php
namespace Ledjin\Bundle\SagepayBundle\DependencyInjection\Factory\Payment;

use Payum\Core\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AbstractPaymentFactory;

class SagepayOnsitePaymentFactory extends AbstractPaymentFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sagepay_onsite';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
            ->scalarNode('vendor')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('sandbox')->defaultTrue()->end()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function addApis(Definition $paymentDefinition, ContainerBuilder $container, $contextName, array $config)
    {
        $apiDefinition = new DefinitionDecorator('payum.sagepay.api.prototype');
        $apiDefinition->replaceArgument(
            1,
            array(
                'vendor' => $config['vendor'],
                'sandbox' => $config['sandbox'],
            )
        );

        $apiDefinition->setPublic(true);
        $apiId = 'payum.context.'.$contextName.'.api';
        $container->setDefinition($apiId, $apiDefinition);
        $paymentDefinition->addMethodCall('addApi', array(new Reference($apiId)));
    }
}
