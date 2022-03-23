<?php

namespace Ospina\CurlCobain; //Este es el mismo definido en composer como src, apartir de acá comienza la estrucura del codigo 

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
     * @var false|resource
     */
    private $ch;

    /**
     * CurlCobain constructor.
     * @param $url
     * @param $method
     */
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
        $this->headers[] = $headers;
        $this->setCurlOption(CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * @return bool|string
     */
    public function makeRequest()
    {
        $resp = curl_exec($this->ch);
        curl_close($this->ch);
        return $resp;
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
    public function setDataAsJson(array $data)
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

}






