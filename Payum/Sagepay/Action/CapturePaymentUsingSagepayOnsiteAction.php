<?php

/*
* This file is part of the Ledjin package.
*
* (c) Alexandr Jeliuc
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Ledjin\Bundle\SagepayBundle\Payum\Sagepay\Action;

use Payum\Core\Exception\LogicException;
use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\PayumBundle\Payum\Action\AbstractCapturePaymentAction;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Currency\Converter\CurrencyConverterInterface;
use Payum\Bundle\PayumBundle\Security\TokenFactory;

/**
 * @author Alexandr Jeliuc <a2xchip@gmail.com>
 */
class CapturePaymentUsingSagepayOnsiteAction extends AbstractCapturePaymentAction
{
    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var CurrencyConverterInterface
     */
    protected $currencyConverter;

    /**
     * @var TokenFactory
     */
    protected $tokenFactory;

    /**
     * Define the Symfony Request
     *
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->httpRequest = $request;
    }

    /**
     * Define sylius currency converter
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function setCurrencyConverter(CurrencyConverterInterface $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * Define token factory
     * @param TokenFactory $tokenFactory 
     */
    public function setTokenFactory(TokenFactory $tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @param PaymentInterface $payment
     * @param TokenInterface   $token
     *
     * @throws LogicException
     */
    protected function composeDetails(PaymentInterface $payment, TokenInterface $token)
    {
        if ($payment->getDetails()) {
            return;
        }

        if (!$this->httpRequest) {
            throw new LogicException('The action can be run only when http request is set.');
        }

        $order = $payment->getOrder();

        $total = $this->currencyConverter->convert($order->getTotal(), $order->getCurrency());

        $details = array(
            'VendorTxCode' => $order->getNumber(),
            'Amount' => number_format($order->getTotal() / 100, 2),
            'Currency' => $order->getCurrency(),
            'Description' => '',
            'NotificationURL' => $this->tokenFactory->createNotifyToken(
                $token->getPaymentName(),
                $payment
            )->getTargetUrl(),
            'BillingSurname' => $order->getBillingAddress()->getLastName(),
            'BillingFirstnames' => $order->getBillingAddress()->getFirstName(),
            'BillingAddress1' => $order->getBillingAddress()->getStreet(),
            'BillingCity' => $order->getBillingAddress()->getCity(),
            'BillingPostCode' => $order->getBillingAddress()->getPostcode(),
            'BillingCountry' => $order->getBillingAddress()->getCountry()->getIsoName(),
            'DeliverySurname' => $order->getShippingAddress()->getLastName(),
            'DeliveryFirstnames' => $order->getShippingAddress()->getFirstName(),
            'DeliveryAddress1' => $order->getShippingAddress()->getStreet(),
            'DeliveryCity' => $order->getShippingAddress()->getCity(),
            'DeliveryPostCode' => $order->getShippingAddress()->getPostcode(),
            'DeliveryCountry' => $order->getShippingAddress()->getCountry()->getIsoName(),
        );

        $payment->setDetails((array) $details);
    }
}
