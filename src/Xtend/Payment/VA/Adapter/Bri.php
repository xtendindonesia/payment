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
     *       'Content-Type' => 'application/json'
     *    ],
     *    'account' => [
     *       'institution_code' => '<institution_code>',  // from config (dev/prod)
     *       'briva_no' => '<briva_no>',            // from config (dev/prod)
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
     * @var string
     */
    protected $timestamp;

    /**
     * @var array
     */
    protected $httpHeaders = [];

    public function __construct(array $configs, bool $sandbox = false)
    {
        $this->setConfigs($configs);
        $this->setHttpHeaders($configs['http_headers']);
        // setup endpoint
        if ($sandbox === true || (isset($configs['sandbox']) ? $configs['sandbox'] : false)) {
            $this->endpoint = 'https://sandbox.partner.api.bri.co.id';
        } else {
            $this->endpoint = 'https://partner.api.bri.co.id';
        }

        // set http_client
	    $this->configs['http_client']['base_uri'] = $this->getEndpoint();
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

    /**
     * Set Timestamp
     *
     * @param string timestamp
     */
    public function setTimestamp(string $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get Timestamp
     */
    public function getTimestamp(): string
    {
        if ($this->timestamp === null) {
            $this->setTimestamp($this->generateTimestamp());
        }

        return $this->timestamp;
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
        $uri = '/v1/briva';
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;
        // compose body request
        $bodyRequest = [
            'institutionCode' => $this->getConfigs()['account']['institution_code'],
            'brivaNo'  => $this->getConfigs()['account']['briva_no'],
            'custCode' => $number,
            'nama'     => $name,
            'amount'   => $amount,
            'keterangan'  => $desc,
            'expiredDate' => $expired->format('Y-m-d H:i:s')
        ];
        $jsonBodyRequest = json_encode($bodyRequest);

        // generate signature & timestamp
        $signature = $this->generateSignature('POST', $uri, $this->getTimestamp(), $jsonBodyRequest);
        $this->httpHeaders['BRI-Signature'] = $signature;
        $this->httpHeaders['BRI-Timestamp'] = $this->getTimestamp();
        $request   = new Request('POST', $url, $this->getHttpHeaders(), $jsonBodyRequest);

        try {
            $response = $this->getClient()->send($request);
            if ($response->getStatusCode() == '200') {
                $jsonResponse = json_decode($response->getBody()->getContents(), true);
                return $jsonResponse;
            }

            $message = $response->getStatusCode() . ':' . $jsonResponse['responseCode'] . ':' . $jsonResponse['responseCode'];
            throw new \RuntimeException($message);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Delete Virtual Account
     *
     * @param  string $number
     * @return array
     */
    public function delete(string $number)
    {
        $institutionCode = $this->getConfigs()['account']['institution_code'];
        $brivaNo = $this->getConfigs()['account']['briva_no'];

        $uri = '/v1/briva';
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;
        
        // Set Body to URL Encoded
        $params = ['institutionCode' => $institutionCode, 'brivaNo' => $brivaNo, 'custCode' => $number];
        $body   = http_build_query($params);

        // generate signature & timestamp
        $signature = $this->generateSignature('DELETE', $uri, $this->getTimestamp(), $body);
        unset($this->httpHeaders['Content-Type']);
        $this->httpHeaders['BRI-Signature'] = $signature;
        $this->httpHeaders['BRI-Timestamp'] = $this->getTimestamp();
        $request  = new Request('DELETE', $url, $this->getHttpHeaders(), $body);
        try {
            $response = $this->getClient()->send($request);
            if ($response->getStatusCode() == '200') {
                $jsonResponse = json_decode($response->getBody()->getContents(), true);
                return $jsonResponse;
            }

            $message = $response->getStatusCode() . ':' . $jsonResponse['responseCode'] . ':' . $jsonResponse['responseCode'];
            throw new \RuntimeException($message);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    public function update(string $number, array $data)
    {
    }

    public function getDetail(string $number)
    {
    }

    /**
     * Get Report
     *
     * @param  \DateTime $startDate
     * @param  \DateTime $endDate
     *
     * @return array|null
     */
    public function getReport(\DateTime $startDate, \DateTime $endDate): ?array
    {
        $urls = [
            '/v1/briva/report',
            $this->getConfigs()['account']['institution_code'],
            $this->getConfigs()['account']['briva_no'],
            $startDate->format('Ymd'),
            $endDate->format('Ymd'),
        ];
        $uri = implode('/', $urls);
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;

        // set signature
        $signature = $this->generateSignature('GET', $uri, $this->getTimestamp());
        $this->httpHeaders['BRI-Signature'] = $signature;
        $this->httpHeaders['BRI-Timestamp'] = $this->getTimestamp();
        // remove content-type
        unset($this->httpHeaders['Content-Type']);
        $request = new Request('GET', $url, $this->getHttpHeaders());
        try {
            $response = $this->getClient()->send($request);
            if ($response->getStatusCode() == '200') {
                $jsonResponse = json_decode($response->getBody()->getContents(), true);
                return $jsonResponse;
            }

            $message = $response->getStatusCode() . ':' . $jsonResponse['responseCode'] . ':' . $jsonResponse['responseCode'];
            throw new \RuntimeException($message);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Get Access Token
     *
     * @return array|null
     */
    public function authorize(): ?array
    {
        $uri = '/oauth/client_credential/accesstoken?grant_type=client_credentials';
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $bodyRequest = [
            'client_id'  => $this->getConfigs()['auth']['client_id'],
            'client_secret' => $this->getConfigs()['auth']['client_secret'],
        ];
        $request  = new Request('POST', $url, $headers, http_build_query($bodyRequest));
        try {
            $response = $this->getClient()->send($request);
            $jsonResponse = json_decode($response->getBody()->getContents(), true);
            return $jsonResponse;
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Generate Timestamp in ISO8601
     *
     * @return string
     */
    public function generateTimestamp(): string
    {
        return gmdate("Y-m-d\TH:i:s.000\Z");
    }

    /**
     * Generate Signature
     *
     * @return string
     */
    public function generateSignature(string $verb, string $uri, string $timestamp, string $body = ''): string
    {
        $secret  = $this->getConfigs()['auth']['client_secret'];
        $token   = $this->getHttpHeaders()['Authorization'];
        $payload = "path=$uri&verb=$verb&token=$token&timestamp=$timestamp&body=$body";
        $signPayload = hash_hmac('sha256', $payload, $secret, true);
        $base64  = base64_encode($signPayload);
        return $base64;
    }
}
