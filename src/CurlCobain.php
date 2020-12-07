<?php

namespace Ospina\CurlCobain; //Este es el mismo definido en composer como src, apartir de acá comienza la estrucura del codigo 

class CurlCobain
{
    public $curl;
    public $url = null;
    public $endpoint = null;
    public $httpmethod = null;
    public $response = null;
    public $contentType = null;
    public $headers = array();
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
    public function execute()
    {
        $this->response = curl_exec($this->curl);
        //curl_close($this->curl);
        return $this->response;
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
            return $this->execute();
        } else {
            $this->setQueryString($queryStrings);
            return $this->execute();
        }
    }
    /**
     * Set Headers for POST request
     *
     * @param string $headerType for example Content type
     * @param string $value for example aplication/json
     * @return void
     */
    public function setHeaders($headerType, $value)
    {
        $header = $headerType . ': ' . $value;
        array_push($this->headers, $header);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }
    /**
     * Set contentType for the request
     *
     * @param string $contentType Desired request's contentType (Multipart,Json)
     * @return void
     */
    public function setContentType(string $contentType)
    {

        switch ($contentType) {
            case ('application/json'):
                $this->setHeaders('Content-Type', 'application/json');
                break;
            case ('json'):
                $this->setHeaders('Content-Type', 'application/json');
                break;

            default:
                $this->setHeaders('Content-Type', 'ptoamo');
                break;
        }
    }
    /**
     * Set body for POST request, if help set to true it will detect content type and convert body to
     * the corresponding encoding.
     * 
     * @param mixed $postBody
     * @param string $contentType If you set content type it will set it using SetContentType function
     * @return void
     */
    public function setPostBody($postBody, string $contentType = '')
    {
        if (isset($postBody)) {

            if (empty($contentType)) {
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postBody); //will set ignoring encoding
            } else {
                $this->setContentType($contentType);
                curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postBody);
            }
        } else {
            throw new \Exception('No body has been defined for post request');
        }
    }
    /**
     * Make post request, if set content type it will auto-convert param an set curlopt contenty type
     * for you.
     *
     * @param mixed $postBody
     * @param string $contentType Default:multipart/form data, can set to json or other.
     * @return void
     */
    public function post($postBody = null, string $contentType = null)
    {
        curl_setopt($this->curl, CURLOPT_POST, true); //turn POST method on

        if (empty($postBody)) {
            return $this->execute();
        } else {
            if ($contentType != null) {
                
                $this->setPostBody($postBody,$contentType);
            } else {
                $this->setPostBody($postBody);
            }
            return $this->execute();
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
