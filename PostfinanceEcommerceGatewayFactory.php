<?php
namespace Payum\PostfinanceEcommerce;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\PostfinanceEcommerce\Action\CaptureAction;
use Payum\PostfinanceEcommerce\Action\NotifyAction;
use Payum\PostfinanceEcommerce\Action\NotifyNullAction;
use Payum\PostfinanceEcommerce\Action\StatusAction;

class PostfinanceEcommerceGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'postfinance_ecommerce',
            'payum.factory_title' => 'Postfinance Ecommerce',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
            'payum.action.notify' => new NotifyAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => Api::TEST
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['sha-in-passphrase', 'sha-out-passphrase', 'pspid'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $postfinanceConfig = [
                    'sha-in-passphrase' => $config['sha-in-passphrase'],
                    'sha-out-passphrase' => $config['sha-out-passphrase'],
                    'pspid' => $config['pspid'],
                    'environment' => $config['environment'],
                    'default_parameters' => isset($config['default_parameters']) ? $config['default_parameters'] : []
                ];

                return new Api($postfinanceConfig);
            };
        }
    }
}
