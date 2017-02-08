<?php

namespace Payum\PostfinanceEcommerce;

use GuzzleHttp\Client;
use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use PostFinance\DirectLink\DirectLinkMaintenanceRequest;
use PostFinance\DirectLink\DirectLinkMaintenanceResponse;
use PostFinance\DirectLink\DirectLinkPaymentRequest;
use PostFinance\DirectLink\DirectLinkPaymentResponse;
use PostFinance\Ecommerce\EcommercePaymentRequest;
use PostFinance\Ecommerce\EcommercePaymentResponse;
use PostFinance\ParameterFilter\ShaInParameterFilter;
use PostFinance\ParameterFilter\ShaOutParameterFilter;
use PostFinance\Passphrase;
use PostFinance\ShaComposer\AllParametersShaComposer;

class Api
{
    const TEST = 'test';
    const PRODUCTION = 'production';

    protected $options = [
        'sha-in-passphrase' => null,
        'sha-out-passphrase' => null,
        'pspid' => null,
        'environment' => self::TEST,
        'default_parameters' => []
    ];

    /**
     * @param array $mir müoptions
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $details
     * @return array
     */
    public function getParameters(ArrayObject $details)
    {
        $params = array_merge($this->options['default_parameters'], (array) $details);

        $params['PSPID'] = $this->options['pspid'];
        $params['SHAIN'] = $this->options['sha-in-passphrase'];

        $signParams = [
            $params['ORDERID'],
            $params['AMOUNT'],
            $params['CURRENCY'],
            $params['PSPID'],
            $params['SHAIN'],
        ];

        $params['SHASIGN'] = sha1(implode('', $signParams));

        return $params;
    }

    /**
     * @return string
     */
    public function getPaymentTargetUrl()
    {
        if ($this->options['environment'] === self::PRODUCTION) {
            return 'https://e-payment.postfinance.ch/ncol/prod/orderstandard.asp';
        }

        return 'https://e-payment.postfinance.ch/ncol/test/orderstandard.asp';
    }

    public function verifyRequestParameters(array $requestParameters) : bool
    {
        $fields = [
            'ORDERID',
            'CURRENCY',
            'AMOUNT',
            'PM',
            'ACCEPTANCE',
            'STATUS',
            'CARDNO',
            'PAYID',
            'NCERROR',
            'BRAND',
        ];

        $signParameters = array_intersect_key($requestParameters, array_flip($fields));
        $sh1hash = sha1(implode($signParameters).$this->options['sha-out-passphrase']);

        return strtoupper($sh1hash) === strtoupper($requestParameters['SHASIGN']);
    }
}
