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
class Services_Amazon_SNS_Topics extends Services_Amazon_SNS_Common
{
    protected $expectedErrorCodes = array(
        500, 400, 403,
    );

    /**
     * When you already created a topic, the API will return the ARN of the already
     * created one. Otherwise, this works as expected.
     *
     * <code>
     * http://sns.us-east-1.amazonaws.com/
     * ?Name=My-Topic
     * &Action=CreateTopic
     * &SignatureVersion=2
     * &SignatureMethod=HmacSHA256
     * &Timestamp=2010-03-31T12%3A00%3A00.000Z
     * &AWSAccessKeyId=(AWS Access Key ID)
     * &Signature=gfzIF53exFVdpSNb8AiwN3Lv%2FNYXh6S%2Br3yySK70oX4%3D
     * </code>
     *
     * @param string $name The name of the topic.
     *
     * @return string The topic's ARN.
     */
    public function add($name)
    {
        $requestUrl = $this->createRequest(array('Name' => $name, 'Action' => 'CreateTopic'));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * Delete a topic.
     *
     * @param string $arn The topic's ARN.
     *
     * @return boolean
     */
    public function delete($arn)
    {
        $requestUrl = $this->createRequest(array('TopicArn' => $arn, 'Action' => 'DeleteTopic'));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * Delete a policy/permission (by label).
     *
     * @param string $arn   The topic's ARN.
     * @param string $label The policy's label.
     *
     * @return boolean
     */
    public function deletePermission($arn, $label)
    {
        $requestUrl = $this->createRequest(
            array(
                'TopicArn' => $arn,
                'Label'    => $label,
                'Action'   => 'RemovePermission'
            )
        );
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * ListTopics.
     *
     * @return array
     */
    public function get()
    {
        $requestUrl = $this->createRequest(array('Action' => 'ListTopics'));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * Retrieve a topic's attributes.
     *
     * @param string $arn The topic's ARN.
     *
     * @return array
     */
    public function getAttributes($arn)
    {
        $requestUrl = $this->createRequest(
            array('Action' => 'GetTopicAttributes', 'TopicArn' => $arn)
        );
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * This is a wrapper around getAttributes() and only returns 'Policy'.
     * 'Policy' is decoded, and then the Statement array is returned.
     *
     * AWS SNS' API has no native 'GetPermission' call.
     *
     * @param string $arn The topic's ARN.
     *
     * @return array
     */
    public function getPermissions($arn)
    {
        $attributes = $this->getAttributes($arn);
        $policy     = json_decode($attributes['Policy']);

        return $policy->Statement;
    }

    /**
     * Set a topic's attribute.
     *
     * @param string $arn       The topic's ARN.
     * @param string $attribute The name of the attribute.
     * @param mixed  $value     The value.
     *
     * @return boolean
     * @throws Services_Amazon_SNS_Exception When $value is not a string, or similar.
     */
    public function setAttribute($arn, $attribute, $value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            throw new Services_Amazon_SNS_Exception("Invalid argument.");
        }

        $requestUrl = $this->createRequest(
            array(
                'Action'         => 'SetTopicAttributes',
                'TopicArn'       => $arn,
                'AttributeValue' => $value,
                'AttributeName'  => $attribute,
            )
        );

        $response = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
     * Set permission on a topic. (SNS: AddPermission)
     *
     * <code>
     * http://sns.us-east-1.amazonaws.com/
     * ?TopicArn=arn%3Aaws%3Asns%3Aus-east-1%3A123456789012%3AMy-Test
     * &ActionName.member.1=Publish
     * &ActionName.member.2=GetTopicAttributes
     * &Label=NewPermission
     * &AWSAccountId.member.1=987654321000
     * &AWSAccountId.member.2=876543210000
     * &Action=AddPermission
     * &SignatureVersion=2
     * &SignatureMethod=HmacSHA256
     * &Timestamp=2010-03-31T12%3A00%3A00.000Z
     * &AWSAccessKeyId=(AWS Access Key ID)
     * &Signature=k%2FAU%2FKp13pjndwJ7rr1sZszy6MZMlOhRBCHx1ZaZFiw%3D
     * </code>
     *
     * @param string $arn
     * @param string $label
     * @param array  $data
     *
     * @return boolean
     * @throws Services_Amazon_SNS_Excpetion When an action is invalid.
     */
    public function setPermissions($arn, $label, array $data)
    {
        static $actions = array(
            "GetTopicAttributes",
            "SetTopicAttributes",
            "AddPermission",
            "RemovePermission",
            "DeleteTopic",
            "Subscribe",
            "ListSubscriptionsByTopic",
            "Publish",
            "Receive",
        );

        $params = array(
            'TopicArn' => $arn,
            'Label'    => $label,
            'Action'   => 'AddPermission',
        );

        $i=1;
        foreach ($data as $accessKeyId => $action) {

            if (!in_array($action, $actions)) {
                throw new Services_Amazon_SNS_Exception("Invalid argument: {$action}");
            }

            $params["AWSAccountId.member.{$i}"] = $accessKeyId;
            $params["ActionName.member.{$i}"]   = $action;

        }

        $requestUrl = $this->createRequest($params);
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }
    
    /**
    * Publish to topic.
    *
    * @param string $arn
    * @param string $message 
    * @param string $subject
    *
    * @return string message id
    */
    public function publish($arn, $message, $subject = '')
    {
        $params = array('Action' => 'Publish', 'TopicArn' => $arn, 'Message' => $message);
        if (!empty($subject)) {
            $params['Subject'] = $subject;
        }

        $requestUrl = $this->createRequest($params);
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
        if (isset($xml->CreateTopicResult)) {
            return (string) $xml->CreateTopicResult->TopicArn;
        }
        if ($xml->getName() == 'DeleteTopicResponse') {
            return true;
        }
        if (isset($xml->ListTopicsResult)) {
            $topics = array();
            foreach ($xml->ListTopicsResult->Topics->member as $member) {
                $topics[] = (string) $member->TopicArn;
            }
            return $topics;
        }
        if ($xml->getName() == 'SetTopicAttributesResponse') {
            return true;
        }
        if (isset($xml->GetTopicAttributesResult)) {
            $attributes = array();
            foreach ($xml->GetTopicAttributesResult->Attributes->entry as $entry) {
                $attributes[(string) $entry->key] = (string) $entry->value;
            }
            return $attributes;
        }
        if ($xml->getName() == 'AddPermissionResponse') {
            return true;
        }
        if ($xml->getName() == 'RemovePermissionResponse') {
            return true;
        }
        if (isset($xml->PublishResult)) {
            return (string) $xml->PublishResult->MessageId;
        }
        var_dump($xml, $xml->getName(), $xml->asXml());
        throw new Services_Amazon_SNS_Exception("Not yet implemented response parser.");
    }
}