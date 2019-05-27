<?php

declare(strict_types = 1);

namespace Xtend\Payment\VA\Adapter;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class Bri implements AdapterInterface
{
    /**
     * @var array
     * [
     *    'http_client' => [
     *       'base_uri' => 'https://developer.bri.co.id',  // from config (dev/prod)
     *       'ssl_verify' => false
     *    ],
     *    'http_headers' => [
     *       'Authorization' => 'Bearer <access_token>', // from database
     *       'X-BRI-KEY' => '<bri_key>', // from config (dev/prod)
     *       'Content-Type' => 'application/json'
     *    ],
     *    'account' => [
     *       'institution_code' => '<institution_code>',  // from config (dev/prod)
     *       'briva_no' => '<briva_no>',            // from config (dev/prod)
     *       'cust_code' => '892837394083',    // from database
     *       'name' => 'Masuno',               // from database
     *    ],
     *    'auth' => [
     *       'client_id' => '<client id>',         // from config (dev/prod)
     *       'client_secret' => '<client_secret>', // from config (dev/prod)
     *       'code' => '<code>'                    // from config (dev/prod)
     *    ],
     *];
     *
     */
    protected $configs;

    /**
     *
     */
    protected $client;

    /**
     * @var array
     */
    protected $httpHeaders;

    public function __construct($configs)
    {
        $this->setConfigs($configs);
        $this->setHttpHeaders($configs['http_headers']);
    }

    public function setConfigs(array $configs)
    {
        $this->configs = $configs;
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function setHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
    }

    public function getHttpHeaders()
    {
        if (empty($this->httpHeaders)) {
            $this->httpHeaders = [
                'Content-Type' => 'application/json',
            ];
        }

        return $this->httpHeaders;
    }


    public function setClient($client)
    {
    
    }

    public function getClient()
    {
        if ($this->client !== null || $this->getConfigs()['http_client'] !== null) {
            $this->client = new HttpClient($this->getConfigs()['http_client']);
        } 

        return $this->client;
    }

    public function create(string $number, float $amount, string $desc, \DateTime $expired): ?array
    {
        $uri = '/v1/api/briva';
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;
        $bodyRequest = [
            'institutionCode' => $this->getConfigs()['account']['institution_code'],
            'brivaNo'  => $this->getConfigs()['account']['briva_no'],
            'custCode' => $this->getConfigs()['account']['cust_code'],
            'nama' => $this->getConfigs()['account']['name'],
            'amount' => $amount,
            'keterangan' => $desc,
            'expiredDate' => $expired->format('Y-m-d H:i:s')
        ];
        $request  = new Request('POST', $url, $this->getHttpHeaders(), json_encode($bodyRequest));
        try {
            $response = $this->getClient()->send($request);
            $jsonResponse = json_decode($response->getBody()->getContents(), true);
            return $jsonResponse;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function delete(string $number)
    {
    }

    public function update(string $number, array $data)
    {
    }

    public function getDetail(string $number)
    {
    }

    public function authorize(): ?array
    {
        $uri = '/v1/api/token';
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;
        $bodyRequest = [
            'grant_type' => 'authorization_code',
            'client_id'  => $this->getConfigs()['auth']['client_id'],
            'client_secret' => $this->getConfigs()['auth']['client_secret'],
            'code' => $this->getConfigs()['auth']['code'],
        ];
        $request  = new Request('POST', $url, $this->getHttpHeaders(), json_encode($bodyRequest));
        try {
            $response = $this->getClient()->send($request);
            $jsonResponse = json_decode($response->getBody()->getContents(), true);
            return $jsonResponse;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
