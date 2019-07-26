<?php

declare(strict_types = 1);

namespace Xtend\Payment\VA\Adapter;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use \Xtend\Payment\Helper\BniEnc;

class Bni implements AdapterInterface
{
    /**
     * @var array
     * [
     *    'http_client' => [
     *       'base_uri' => 'https://apibeta.bni-ecollection.com',  // from config (dev/prod)
     *    ],
     *    'http_headers' => [
     *    ],
     *    'account' => [
     *       'va_prefix' => '988'
     *    ],
     *    'auth' => [
     *       'client_id' => '<client id>',         // from config (dev/prod)
     *       'client_secret' => '<client_secret>', // from config (dev/prod)
     *    ],
     *];
     *
     */
    protected $configs;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var bool
     */
    protected $sandbox;

    /**
     *
     */
    protected $client;

    /**
     * @var array
     */
    protected $httpHeaders = [];

    /**
     * Constructor
     *
     * @param array $configs
     * @param bool  $sandbox
     * @void
     */
    public function __construct(array $configs, bool $sandbox = false)
    {
        $this->setConfigs($configs);
        // setup endpoint
        if ($sandbox === true || (isset($configs['sandbox']) ? $configs['sandbox'] : false)) {
            $this->endpoint = 'https://apibeta.bni-ecollection.com';
        } else {
            $this->endpoint = 'https://api.bni-ecollection.com';
        }

        // set http_client
        $this->configs['http_client']['base_uri']   = $this->getEndpoint();
        $this->configs['http_client']['ssl_verify'] = false;
        // set http_headers
        $this->configs['http_headers']['Accept-Encoding'] = 'gzip, deflate';
        $this->configs['http_headers']['Accept-Language'] = 'en-US,en;q=0.8,id;q=0.6';
        $this->configs['http_headers']['Cache-Control'] = 'max-age=0';
        $this->configs['http_headers']['Content-Type']  = 'application/json';
        $this->configs['http_headers']['Connection'] = 'keep-alive';
    }

    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
    }

    public function getConfigs(): array
    {
        return $this->configs;
    }

    public function setSandbox(bool $sandbox)
    {
        $this->sandbox = $sandbox;
    }

    public function getSandbox(): bool
    {
        return $this->sandbox;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
    }

    public function getHttpHeaders()
    {
        if ($this->httpHeaders !== null || $this->getConfigs()['http_headers'] !== null) {
            $this->httpHeaders = $this->getConfigs()['http_headers'];
        }

        return $this->httpHeaders;
    }


    public function setClient($client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        if ($this->client !== null || $this->getConfigs()['http_client'] !== null) {
            //print_r($this->getConfigs()['http_client']);
            $this->client = new HttpClient($this->getConfigs()['http_client']);
        }

        return $this->client;
    }

    /**
     * Create Virtual Account
     *
     * @param  string   $number
     * @param  float    $amount
     * @param  string   $name
     * @param  string   $desc
     * @param  DateTime $expired
     * @return array|null
     *
     */
    public function create(string $number, float $amount, string $name, string $desc, \DateTime $expired): ?array
    {
        $clientId = $this->getConfigs()['auth']['client_id'];
        $vaPrefix = $this->getConfigs()['account']['va_prefix'];
        $data = [
            'client_id' => $clientId,
            'type'   => 'createBilling',
            'trx_id' => mt_rand(), // fill with Billing ID
            'trx_amount'   => $amount,
            'billing_type' => 'c',
            'datetime_expired' => $expired->format('c'),
            'virtual_account'  => $vaPrefix . $clientId . $number,
            'customer_name'  => $name,
            'description'    => $desc,
            'customer_email' => '',
            'customer_phone' => '',
        ];
        try {
            $response = $this->sendRequest($data);
            return $response;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Update VA
     *
     * @param  string trxId
     * @param  array  data
     * @return null|array
     * @throw  \RuntimeException
     */
    public function update(string $trxId, array $data)
    {
        $clientId = $this->getConfigs()['auth']['client_id'];
        $newData  = [
            'trx_id' => $trxId,
            'type'   => 'updateBilling',
            'client_id' => $clientId,
        ];

        if (! isset($data['trx_amount'])) {
            throw new \RuntimeException('trx_amount required');
        }

        if (! isset($data['customer_name'])) {
            throw new \RuntimeException('customer_name required');
        }

        $newData['customer_name'] = $data['customer_name'];
        $newData['trx_amount']    = $data['trx_amount'];
        $newData['datetime_expired'] = is_null($data['datetime_expired']) ? null : $data['datetime_expired']->format('c');
        $newData['customer_email'] = $data['customer_email'] ?? '';
        $newData['customer_phone'] = $data['customer_phone'] ?? '';
        $newData['description'] = $data['description'] ?? '';
        print_r($newData);
        try {
            $response = $this->sendRequest($newData);
            return $response;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Get Detail VA
     *
     * @param  string trxId
     * @return null|array
     */
    public function getDetail(string $trxId)
    {
        $clientId  = $this->getConfigs()['auth']['client_id'];
        $data = ['client_id' => $clientId, 'type'   => 'inquiryBilling', 'trx_id' => $trxId];
        try {
            $response = $this->sendRequest($data);
            return $response;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function delete(string $number)
    {
    }

    /**
     * Send HTTP Request to BNI Ecollection Server
     *
     * @param  array $data
     * @return @array
     * @throw  \RuntimeException
     */
    private function sendRequest($data)
    {
        $url = $this->getConfigs()['http_client']['base_uri'];
        $clientId  = $this->getConfigs()['auth']['client_id'];
        $secretKey = $this->getConfigs()['auth']['client_secret'];
        $hashData  = BniEnc::encrypt($data, $clientId, $secretKey);
        $jsonBodyRequest = json_encode(['client_id' => $clientId, 'data' => $hashData]);
        $request  = new Request('POST', $url, $this->getHttpHeaders(), $jsonBodyRequest);
        try {
            $response = $this->getClient()->send($request);
            // print_r($data);
            if ($response->getStatusCode() == '200') {
                $jsonResponse = json_decode($response->getBody()->getContents(), true);
                if ($jsonResponse['status'] !== '000') {
                    $message = $jsonResponse['status'] . ': ' . $jsonResponse['message'];
                    throw new \RuntimeException($message);
                }

                $decryptResponse = BniEnc::decrypt($jsonResponse['data'], $clientId, $secretKey);
                return $decryptResponse;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
