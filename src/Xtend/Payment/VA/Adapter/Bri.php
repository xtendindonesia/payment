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
    protected $httpHeaders;

    public function __construct(array $configs, bool $sandbox = false)
    {
        $this->setConfigs($configs);
        $this->setHttpHeaders($configs['http_headers']);
        // setup endpoint
        if ($sandbox === true || (isset($configs['sandbox']) ? $configs['sandbox'] : false)) {
            $this->endpoint = 'https://partner.api.bri.co.id';
        } else {
            $this->endpoint = 'https://sandbox.partner.api.bri.co.id';
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

    protected function encodeBody($institutionCode, $brivaNo, $custCode)
    {
        $params  = compact(['institutionCode', 'brivaNo', 'custCode']);
        return http_build_query($params);
    }

    public function delete(string $number)
    {
        $institutionCode = $this->getConfigs()['account']['institution_code'];
        $brivaNo = $this->getConfigs()['account']['briva_no'];

        $uri = '/v1/api/briva';
        $url = $this->getConfigs()['http_client']['base_uri'] . $uri;
        
        // Set Header
        $headers = 'application/x-www-form-urlencoded';
        $currentHeaders  = $this->getHttpHeaders();
        $currentHeaders['Content-Type'] = $headers;

        // Set Body to URL Encoded
        $data =  $this->encodeBody($institutionCode, $brivaNo, $number);
        $request  = new Request('DELETE', $url, $currentHeaders,$data);
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

    public function getReport(\DateTime $startDate, \DateTime $endDate): ?array
    {
        $urls = [
            '/v1/api/briva/report',
            $this->getConfigs()['account']['institution_code'],
            $this->getConfigs()['account']['briva_no'],
            $startDate->format('Ymd'),
            $endDate->format('Ymd'),
        ];
        $url = $this->getConfigs()['http_client']['base_uri'] . implode('/', $urls);
        echo $url, PHP_EOL;
        $request  = new Request('GET', $url, $this->getHttpHeaders());
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
}
