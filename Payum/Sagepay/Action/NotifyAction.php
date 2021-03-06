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

use Doctrine\Common\Persistence\ObjectManager;
use Ledjin\Sagepay\Api\ApiInterface;
use Payum\Bundle\PayumBundle\Request\ResponseInteractiveRequest;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\NotifyRequest;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\PayumBundle\Payum\Action\AbstractPaymentStateAwareAction;
use Sylius\Bundle\PayumBundle\Payum\Request\StatusRequest;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @author Alexandr Jeliuc <a2xchip@gmail.com>
 */
class NotifyAction extends AbstractPaymentStateAwareAction
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var RepositoryInterface
     */
    protected $paymentRepository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $identifier;

    public function __construct(
        ApiInterface $api,
        RepositoryInterface $paymentRepository,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $objectManager,
        FactoryInterface $factory,
        $identifier
    ) {
        parent::__construct($factory);

        $this->api               = $api;
        $this->paymentRepository = $paymentRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->objectManager     = $objectManager;
        $this->identifier        = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request NotifyRequest */
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $details = $request->getNotification();

        if (!$this->api->verifyHash($details)) {
            throw new BadRequestHttpException('Hash cannot be verified.');
        }

        if (empty($details['ORDERID'])) {
            throw new BadRequestHttpException('Order id cannot be guessed');
        }

        $payment = $this->paymentRepository->findOneBy(array($this->identifier => $details['ORDERID']));

        if (null === $payment) {
            throw new BadRequestHttpException('Paymenet cannot be retrieved.');
        }

        if ((int) $details['AMOUNT'] !== $payment->getAmount()) {
            throw new BadRequestHttpException('Request amount cannot be verified against payment amount.');
        }

        // Actually update payment details
        $details = array_merge($payment->getDetails(), $details);
        $payment->setDetails($details);

        $status = new StatusRequest($payment);
        $this->payment->execute($status);

        $nextState = $status->getStatus();

        $this->updatePaymentState($payment, $nextState);

        $this->objectManager->flush();

        throw new ResponseInteractiveRequest(new Response('OK', 200));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof NotifyRequest;
    }
}
