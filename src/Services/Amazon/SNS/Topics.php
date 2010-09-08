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
     * <code>
     * http://sns.us-east-1.amazonaws.com/
     * ?Name=My-Topic
     * &Action=CreateTopic
     * &SignatureVersion=2
     * &SignatureMethod=HmacSHA256
     * &Timestamp=2010-03-31T12%3A00%3A00.000Z
     * &AWSAccessKeyId=(AWS Access Key ID)
     * &Signature=gfzIF53exFVdpSNb8AiwN3Lv%2FNYXh6S%2Br3yySK70oX4%3D
     */
    public function add($name)
    {
        $requestUrl = $this->createRequest(array('Name' => $name, 'Action' => 'CreateTopic'));
        $response   = $this->makeRequest($requestUrl);

        return $this->parseResponse($response);
    }

    /**
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
     */
    public function addPermission()
    {
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
     * List.
     */
    public function get()
    {
    }

    public function getAttributes()
    {
    }

    public function setAttributes()
    {
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
        throw new Services_Amazon_SNS_Exception("Not yet implemented response parser.");
    }
}