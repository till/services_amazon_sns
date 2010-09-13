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
class Services_Amazon_SNS
{
    /**
     * @const Indicates that the user has been denied access to the requested resource.
     */
    const AuthorizationError = 403;

    /**
     * @const Indicates an internal service error.
     */
    const InternalError = 500;

    /**
     * @const Indicates that a request parameter does not comply with the associated constraints.
     */
    const InvalidParameter = 400;

    /**
     * @const Indicates that the requested resource does not exist.
     */
    const NotFound = 404;

    /**
     * @var string
     * @see self::__construct()
     */
    protected $accessKeyId, $secretAccessKey = null;

    /**
     * @var string $zone The availability zone.
     */
    protected $zone = 'us-east-1';

    /**
     * @var array $subs Hosts instances of the sub classes.
     */
    protected static $subs = array();

    /**
     * CTOR
     *
     * @param string $accessKeyId     AWS Access Key ID.
     * @param string $secretAccessKey AWS Secret Access Key.
     *
     * @return $this
     */
    public function __construct($accessKeyId, $secretAccessKey)
    {
        $this->accessKeyId     = $accessKeyId;
        $this->secretAccessKey = $secretAccessKey;
    }

    /**
     * Autoloader.
     *
     * @param string $className E.g., Services_Amazon_SNS_Exception
     *
     * @return boolean
     */
    public static function autoload($className)
    {
        $file = $className;
        if (substr($file, 0, 15) == 'Services_Amazon_') {
            $file = substr($file, 0, 15);
            $file = dirname(__FILE__) . '/' . $file;
        }
        $file = str_replace('_', '/', $file) . '.php';

        include $file;
    }

    /**
     * Return the AWS Access Key ID
     *
     * @return string
     */
    public function getAccessKeyId()
    {
        return $this->accessKeyId;
    }

    /**
     * Return the AWS Secret Access Key
     *
     * @return string
     */
    public function getSecretAccessKey()
    {
        return $this->secretAccessKey;
    }

    /**
     * Return the availability zone.
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set the availability zone.
     *
     * @param string $zone
     *
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;
        return $this;
    }

    /**
     * 
     * @return mixed
     */
    public function __get($var)
    {
        $var = strtolower($var);

        if (isset(self::$subs[$var])) {
            return self::$subs[$var];
        }
        switch ($var) {
        case 'topics':
            return self::$subs[$var] = new Services_Amazon_SNS_Topics($this->accessKeyId, $this->secretAccessKey, $this->zone);
            break;
        case 'subscriptions':
            return self::$subs[$var] = new Services_Amazon_SNS_Subscriptions($this->accessKeyId, $this->secretAccessKey, $this->zone);
            break;
        default:
            throw new Services_Amazon_SNS_Exception("Unknown: {$var}");
        }
    }
}

spl_autoload_register(array('Services_Amazon_SNS', 'autoload'));