<?php

namespace Ospina\CurlCobain;

/**
 *
 */
class CurlCobain
{
    /**
     * @var
     */
    public $url;
    /**
     * @var
     */
    public $finalUrl;
    /**
     * @var mixed|string
     */
    public $method;
    /**
     * @var
     */
    public $data;
    /**
     * @var
     */
    public $queryParams;
    /**
     * @var
     */
    public $headers;
    /**
     * @var
     */
    public $cookies;
    /**
     * @var bool
     */
    public $requireSSL = false;
    /**
     * CurlCobain constructor.
     * @param $url
     * @param $method
     */

    public $mc;
    /**
     * @var false|resource
     */
    public $multipleCurlStorer;
    private $ch;
    /**
     * @var
     */
    private $statusCode;

    public function __construct($url, $method = 'GET')
    {
        $this->ch = curl_init();
        $this->url = $url;
        $this->method = $method;
        $this->basicSetUp();

    }

    /**
     * @return void
     */
    private function basicSetUp(): void
    {

        $this->setCurlOption(CURLOPT_URL, $this->url);
        $this->setCurlOption(CURLOPT_RETURNTRANSFER, true); //Get text instead of void
        $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, $this->requireSSL);
        $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->requireSSL);
        //handle type of http method

        if ($this->method !== 'GET' && $this->method !== 'POST') {
            $this->setCurlOption(CURLOPT_CUSTOMREQUEST, $this->method);
        } else {
            $this->setCurlOption(CURLOPT_POST, $this->method === 'POST');
        }

    }

    /**
     * @param $option
     * @param $value
     * @return void
     */
    public function setCurlOption($option, $value): void
    {
        curl_setopt($this->ch, $option, $value);
    }

    public static function createPararellInstance()
    {
        $paralellCurl = new self('');
        $paralellCurl->initializeMC();
        return $paralellCurl;
    }

    private function initializeMC()
    {
        $this->mc = curl_multi_init();

    }

    /**
     * @param array $queryParams
     * @return void
     */
    public function setQueryParamsAsArray(array $queryParams): void
    {
        foreach ($queryParams as $key => $value) {
            $this->setQueryParam($key, $value);
        }
    }

    /**
     * @param string $fieldName
     * @param string $value
     * @return void
     */
    public function setQueryParam(string $fieldName, string $value)
    {
        $this->queryParams[$fieldName] = $value;

        $this->buildUrl();
    }

    /**
     * @return void
     */
    private function buildUrl()
    {
        if (count($this->queryParams) === 0) {
            $this->finalUrl = $this->url;
        } else {
            $this->finalUrl = $this->url . '?' . http_build_query($this->queryParams);
        }
        $this->setCurlOption(CURLOPT_URL, $this->finalUrl);
    }

    /**
     * @param array $headers
     * @return void
     */
    public function setHeadersAsArray(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
    }

    /**
     * @param string $headerName
     * @param string $headerValue
     * @return void
     */
    public function setHeader(string $headerName, string $headerValue): void
    {
        $this->headers[] = $headerName . ': ' . $headerValue;
        $this->setCurlOption(CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * @return bool|string
     */
    public function makeRequest($close = true)
    {
        $resp = curl_exec($this->ch);
        $this->updateStatusCode();
        if ($close) {
            $this->close();
        }
        return $resp;
    }

    /**
     * @return void
     */
    public function updateStatusCode(): void
    {
        $this->statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

    }

    /**
     * @return void
     */
    public function close(): void
    {
        curl_close($this->ch);
    }

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return void
     */
    public function enableSSL()
    {
        $this->requireSSL = true;
        $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->requireSSL);
    }

    /**
     * @return void
     */
    public function disableSSL()
    {
        $this->requireSSL = false;
        $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, $this->requireSSL);
    }

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
        $this->setCurlOption(CURLOPT_POST, $this->method === 'POST');
    }

    /**
     * @param array $data
     * @return void
     */
    public function setDataAsJson(array $data): void
    {
        $this->setCurlOption(CURLOPT_POSTFIELDS, json_encode($data));
        $this->setHeader('Content-Type', 'application/json');
    }

    /**
     * @param array $data
     * @return void
     */
    public function setDataAsFormUrlEncoded(array $data)
    {
        $postFields = http_build_query($data);

        $this->setCurlOption(CURLOPT_POSTFIELDS, $postFields);
        $this->setHeader('Content-Type', 'application/x-www-form-urlencoded');

    }

    public function addInstanteToPool(CurlCobain $curl)
    {
        $this->multipleCurlStorer[] = $curl->getCurlInstance();
        curl_multi_add_handle($this->mc, $curl->getCurlInstance());
    }

    public function getCurlInstance()
    {
        return $this->ch;
    }

    public function makePararellRequest(): void
    {
        do {
            curl_multi_exec($this->mc, $running);
            curl_multi_select($this->mc);
        } while ($running > 0);
    }

    public function getParellRequestResponse()
    {
        $response = [];
        foreach ($this->multipleCurlStorer as $value) {
            $response[] = curl_multi_getcontent($value);
        }
        return $response;
    }


}






