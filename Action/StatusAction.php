<?php

namespace Payum\PostfinanceEcommerce\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use PostFinance\Ecommerce\EcommercePaymentResponse;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param $request GetStatusInterface
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['STATUS']) || !strlen($details['STATUS'])) {
            $request->markNew();

            return;
        }

        switch ($details['STATUS']) {
            case EcommercePaymentResponse::STATUS_AUTHORISED:
                $request->markAuthorized();
                break;
            case EcommercePaymentResponse::STATUS_PAYMENT_REQUESTED:
            case EcommercePaymentResponse::STATUS_PAYMENT:
            # change to const as soon as PR is merged and STATUS_AUTHORISATION_CANCELLATION_WAITING is available
            case 61:
                $request->markCaptured();
                break;
            case EcommercePaymentResponse::STATUS_INCOMPLETE_OR_INVALID:
            case EcommercePaymentResponse::STATUS_AUTHORISATION_REFUSED:
            case EcommercePaymentResponse::STATUS_PAYMENT_REFUSED:
                $request->markFailed();
                break;
            case EcommercePaymentResponse::STATUS_REFUND:
                $request->markRefunded();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
