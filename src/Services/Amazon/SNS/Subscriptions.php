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
class Services_Amazon_SNS_Subscriptions extends Services_Amazon_SNS_Common
{
    protected $expectedErrorCodes = array(
        500, 400, 403
    );
    
    /**
    * Create a subscription.
    *
    * @param string $arn      topic arn
    * @param string $protocol
    * @param string $endpoint
    *
    * @return string subscription's arn (can be 'pending confirmation' message)
    */
    public function subscribe($arn, $protocol, $endpoint)
    {
        static $protocols = array(
            "http",
            "https",
            "email",
            "email-json",
            "sqs",
        );

        if (!in_array($protocol, $protocols)) {
            throw new Services_Amazon_SNS_Exception("Invalid protocol: {$protocol}");
        }

        $requestUrl = $this->createRequest(
            array(
                'TopicArn' => $arn, 
                'Protocol' => $protocol, 
                'Endpoint' => $endpoint, 
                'Action'   => 'Subscribe'
            )
        );
        
        $response = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }
    
    /**
    * Delete a subscription.
    *
    * @param string arn subscription arn
    *
    * @return boolean
    */
    public function unsubscribe($arn)
    {
        $requestUrl = $this->createRequest(array('SubscriptionArn' => $arn, 'Action' => 'Unsubscribe'));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }
    
    /**
    * Confirm a subscription.
    *
    * @param string $arn           topic arn
    * @param string $token
    * @param string $authenticate 'true' if authentication on unsubscribe is requested
    *
    * @return string subscription's arn
    */
    public function confirm($arn, $token, $authenticate = '')
    {
        $requestParams = array('TopicArn' => $arn, 'Token' => $token);
        if (!empty($authenticate)) {
            if ($authenticate != 'true') {
                throw new Services_Amazon_SNS_Exception("Invalid parameter: {$authenticate}");
            }
            
            $requestParams['AuthenticateOnUnsubscribe'] = $authenticate;
        }
        
        $requestUrl = $this->createRequest($requestParams);
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
    * Return list of subscriptions.
    *
    * @return array
    */
    public function get()
    {
        $requestUrl = $this->createRequest(array('Action' => 'ListSubscriptions'));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }
    
    /**
    * Return list of subscriptions by topic.
    *
    * @param string $arn
    *
    * @return array
    */
    public function getByTopic($arn)
    {
        $requestUrl = $this->createRequest(array('Action' => 'ListSubscriptionsByTopic', 'TopicArn' => $arn));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * This is a parser for the successful response from the various actions.
     *
     * The chain is: {@link self::parseResponse()}, then here.
     *
     * @return mixed
     * @throws Services_Amazon_SNS_Exception If we couldn't match anything.
     */
    protected function responseParser(SimpleXMLElement $xml)
    {
        if (isset($xml->SubscribeResult)) {
            return (string) $xml->SubscribeResult->SubscriptionArn;
        }
        if ($xml->getName() == 'UnsubscribeResponse') {
            return true;
        }
        if (isset($xml->ConfirmSubscriptionResult)) {
            return (string) $xml->ConfirmSubscriptionResult->SubscriptionArn;
        }
        
        $list = false;
        if (isset($xml->ListSubscriptionsResult)) {
            $list  = true;
            $token = 'ListSubscriptionsResult';
        }
        if (isset($xml->ListSubscriptionsByTopicResult)) {
            $list  = true;
            $token = 'ListSubscriptionsByTopicResult';
        }
        if ($list) {
            $subscriptions = array();
            foreach ($xml->$token->Subscriptions->member as $member) {
                $subscriptions[] = array(
                    'topic' => (string) $member->TopicArn, 
                    'subscription' => (string) $member->SubscriptionArn,
                    'protocol' => (string) $member->Protocol,
                    'owner' => (string) $member->Owner,
                    'endpoint' => (string) $member->Endpoint
                );
            }
            return $subscriptions;
        }

        var_dump($xml, $xml->getName(), $xml->asXml());
        throw new Services_Amazon_SNS_Exception("Not yet implemented response parser.");
    }
}