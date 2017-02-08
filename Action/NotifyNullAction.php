<?php

namespace Payum\PostfinanceEcommerce\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\GetToken;
use Payum\Core\Request\Notify;

class NotifyNullAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param $request GetStatusInterface
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $this->gateway->execute($httpRequest = new GetHttpRequest());

        $tokenHash = $httpRequest->query['COMPLUS'];
        if (empty($tokenHash)) {
            throw new HttpResponse('The notification is invalid', 500);
        }

        $this->gateway->execute($getToken = new GetToken($tokenHash));
        $this->gateway->execute(new Notify($getToken->getToken()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() === null
            ;
    }
}
