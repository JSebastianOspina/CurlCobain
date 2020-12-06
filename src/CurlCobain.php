<?php

namespace Ospina\CurlCobain; //Este es el mismo definido en composer como src, apartir de acá comienza la estrucura del codigo 

class CurlCobain
{
    public $curl;
    public $url = null;
    public $endpoint = null;
    public $httpmethod = null;
    public $response = null;
    /**
     * Constructor 
     *
     * @param string $url The request url.
     */
    public function __construct($url)
    {

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true); //This is required in order to see the response
        $this->setUrl($url);
    }
    /**
     * Set endpoint for url
     *
     * @param string $endpoint
     * @return void
     */
    public function setEndpoint($endpoint)
    {
        if (empty($this->url)) {
            throw new \Exception("You can not add an endpoint to an empty url");
        } else {
            $url = $this->url . '/' . $endpoint;
            $this->setUrl($url);
        }
    }
    /**
     * Set the url for the request
     *
     * @param string $url
     * @return void
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
    }
    /**
     * Set Query Strings for desired get HTTP Request
     *
     * @param array $queryStrings Http get query strings
     * @return void
     */
    public function setQueryString(array $queryStrings)
    {
        if (is_array($queryStrings)) {
            if (isset($this->url)) {
                $this->queryString = http_build_query($queryStrings);
                $this->url = $this->url . '?' . $this->queryString;
                curl_setopt($this->curl, CURLOPT_URL, $this->url);
            } else {
                throw new \Exception('Can not add query string to null URL');
            }
        }
    }
    /**
     * Send Get request
     *
     * @param array $queryStrings
     * @return string response
     */
    public function get(array $queryStrings = null)
    {

        if ($queryStrings == null) {
            $this->response = curl_exec($this->curl);
            return $this->response;
        } else {
            $this->setQueryString($queryStrings);
            $this->response = curl_exec($this->curl);
            return $this->response;
        }
    }
    public function setPostBody($postBody)
    {
        if (is_array($postBody) && isset($postBody)) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postBody);
        }
    }
    /**
     * Make post request
     *
     * @param array $postBody
     * @return void
     */
    public function post(array $postBody = null)
    {
        curl_setopt($this->curl, CURLOPT_POST, true); //turn POST method on

        if (empty($postBody)) {
            return curl_exec($this->curl);
        } else {
            $this->setPostBody($postBody);
            return curl_exec($this->curl);
        }
    }
    public function getResponse()
    {

        curl_exec($this->curl);
    }
    /**
     * Get HTTP status code of last request.
     *
     * @return string
     */
    public function getLastStatusCode()
    {
        $statusCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        return $statusCode;
    }
    public function json()
    {
        return json_decode($this->response);
    }
}
