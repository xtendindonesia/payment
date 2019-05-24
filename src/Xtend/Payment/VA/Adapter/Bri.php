<?php

declare(strict_types = 1);

namespace Xtend\Payment\VA\Adapter;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class Bri implements AdapterInterface
{
    protected $configs;

    protected $client;

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

    public function create(string $number, float $amount, string $desc, \DateTime $expired)
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
            echo 'Success: ' . $response->getStatusCode();
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
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
}
