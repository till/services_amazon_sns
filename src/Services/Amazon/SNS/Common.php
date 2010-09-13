<?php
/**
 * +-----------------------------------------------------------------------+
 * | Copyright (c) 2010, Till Klampaeckel                                  |
 * | All rights reserved.                                                  |
 * |                                                                       |
 * | Redistribution and use in source and binary forms, with or without    |
 * | modification, are permitted provided that the following conditions    |
 * | are met:                                                              |
 * |                                                                       |
 * | o Redistributions of source code must retain the above copyright      |
 * |   notice, this list of conditions and the following disclaimer.       |
 * | o Redistributions in binary form must reproduce the above copyright   |
 * |   notice, this list of conditions and the following disclaimer in the |
 * |   documentation and/or other materials provided with the distribution.|
 * | o The names of the authors may not be used to endorse or promote      |
 * |   products derived from this software without specific prior written  |
 * |   permission.                                                         |
 * |                                                                       |
 * | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
 * | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
 * | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
 * | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
 * | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
 * | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
 * | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
 * | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
 * | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
 * | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
 * | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
 * |                                                                       |
 * +-----------------------------------------------------------------------+
 * | Author: Till Klampaeckel <till@php.net>                               |
 * +-----------------------------------------------------------------------+
 *
 * PHP version 5
 *
 * @category Services
 * @package  Services_Amazon_SNS
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  SVN: $Id$
 * @link     http://github.com/till/services_amazon_sns
 */

/**
 * Services_Amazon_SNS
 *
 * @category Services
 * @package  Services_Amazon_SNS
 * @author   Till Klampaeckel <till@php.net>          
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  Release: @package_version@
 * @link     http://github.com/till/services_amazon_sns
 */
abstract class Services_Amazon_SNS_Common
{
    /**
     * @var string
     * @see self::__construct()
     */
    protected $accessKeyId, $secretAccessKey = null;

    /**
     * @var string $endpoint The AWS SNS endpoint.
     */
    protected $endpoint = 'http://sns.%s.amazonaws.com/';

    /**
     * @var string $zone;
     */
    protected $zone;

    /**
     * CTOR
     *
     * @param string $accessKeyId     AWS Access Key ID
     * @param string $secretAccessKey AWS Secret Access Key.
     * @param string $zone            The availability zone.
     *
     * @return $this
     */
    public function __construct($accessKeyId, $secretAccessKey, $zone)
    {
        $this->accessKeyId     = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
        $this->zone            = $zone;
    }

    /**
     * Create the request and sign it.
     *
     * @param array $params The parameters for the request.
     *
     * @return string A URL.
     * @uses   self::$accessKeyId
     * @uses   self::createSignature()
     * @uses   self::getEndpoint()
     */
    protected function createRequest(array $params)
    {
        $params['Timestamp']        = gmdate('c');
        $params['AWSAccessKeyId']   = $this->accessKeyId;

        $params      = $this->createSignature($params);
        $queryString = $this->http_build_query2($params);

        return $this->getEndpoint() . '?' . $queryString;
    }

    /**
    * Implementation of http_build_query which complies with RFC 1738. SNS does not accept spaces encoded as +.
    * Adapted from http://php.net/manual/en/function.http-build-query.php#90438
    *
    * @param array $data query parameters
    *
    * @return string
    */
    protected function http_build_query2($data) 
    { 
        $ret = array(); 
        $sep = '';
        foreach ((array)$data as $k => $v) { 
            if (is_array($v) || is_object($v)) { 
                array_push($ret, http_build_query($v, '', $sep, $k)); 
            } else { 
                array_push($ret, $k.'='.rawurlencode($v)); 
            } 
        } 
        if (empty($sep)) {
            $sep = ini_get('arg_separator.output'); 
        }
    
        return implode($sep, $ret);
    }
        
    /**
     * Blatantly stolen/adapted from {@link Services_Amazon_EC2::signParameters()}.
     *
     * This function takes all parameters, and returns them with the Signature added.
     *
     * @param array $params The parameters.
     *
     * @return array
     * @uses   Crypt_HMAC2
     * @uses   self::getEndpoint()
     */
    protected function createSignature(array $params)
    {
        unset($params['Signature']);

        try {
            // try first to use SHA-256
            $hmac   = new Crypt_HMAC2($this->secretAccessKey, 'SHA256');
            $method = 'HmacSHA256';
        } catch (Crypt_HMAC2_Exception $e) {
            // if SHA-256 is not available, use SHA-1
            $hmac   = new Crypt_HMAC2($this->secretAccessKey, 'SHA1');
            $method = 'HmacSHA1';
        }

        $params['SignatureMethod']  = $method;
        $params['SignatureVersion'] = 2;

        ksort($params);

        $url  = new Net_URL2($this->getEndpoint());
        $data = 'GET' . "\n" . $url->getHost() . "\n" . '/' . "\n" . $this->http_build_query2($params);

        $signature = $hmac->hash($data, Crypt_HMAC2::BINARY);

        // $params['SignatureVersion'] = 2;
        // $params['SignatureMethod']  = $method;

        // Amazon wants the signature value base64-encoded
        $params['Signature'] = base64_encode($signature);

        return $params;
    }

    /**
     * Create the endpoint.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        return sprintf($this->endpoint, $this->zone);
    }

    /**
     * Make a request.
     *
     * @param string $url The URL to make the request against.
     *
     * @return HTTP_Request2_Response
     * @uses   HTTP_Request2
     */
    protected function makeRequest($url)
    {
        $req = new HTTP_Request2($url);
        return $req->send();
    }

    /**
     * Parse error XML from SNS.
     *
     * @param string $body The response body.
     *
     * @return string A hopefully well formatted error message.
     */
    protected function parseErrorResponse($body)
    {
        $body = trim($body);
        if ($body == '<UnknownOperationException/>') {
            return 'Unknown Operation';
        }
        $xml  = new SimpleXMLElement($body);
        return sprintf(
            '%s: %s (%s)',
            (string) $xml->Error->Sender,
            (string) $xml->Error->Message,
            (string) $xml->Error->Code
        );
    }

    /**
     * Parse the response and find out if this is an error.
     *
     * @param HTTP_Request2_Response $response The response object.
     *
     * @return
     * @throws Services_Amazon_SNS_Exception
     */
    protected function parseResponse(HTTP_Request2_Response $response)
    {
        if (substr($response->getStatus(), 0, 2) != '20') {
            throw new Services_Amazon_SNS_Exception($this->parseErrorResponse($response->getBody()));
        }
        $xml = new SimpleXMLElement($response->getBody());
        return $this->responseParser($xml);
    }

    /**
     * Request response parsing is to be done in the actual implementation.
     *
     * @param SimpleXMLElement $xml
     *
     * @return void
     * @see    self::parseRequest()
     */
    abstract protected function responseParser(SimpleXMLElement $xml);
}