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
 * @category Testing
 * @package  Services_Amazon_SNS
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  SVN: $Id$
 * @link     http://github.com/till/services_amazon_sns
 */

/**
 * Tests for {@link Services_Amazon_SNS_Topics}
 *
 * @category Testing
 * @package  Services_Amazon_SNS
 * @author   Till Klampaeckel <till@php.net>          
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  Release: @package_version@
 * @link     http://github.com/till/services_amazon_sns
 */
class TopicTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var array $config The config array.
     */
    protected $config;

    public function setUp()
    {
        $configFile = dirname(__FILE__) . '/test-config.php';
        if (!file_exists($configFile)) {
            $this->fail('These tests require a test-config.php.');
        }
        $this->config = include $configFile;

        $this->instance = new Services_Amazon_SNS(
            $this->config['accessKeyId'],
            $this->config['secretAccessKey']
        );
    }

    public function testSet()
    {
        $topics = $this->instance->topics;
        $this->assertType('Services_Amazon_SNS_Topics', $topics);
    }

    public function testCreate()
    {
        $topics   = $this->instance->topics;
        $topicArn = $topics->add('foo');
        $this->assertNotEquals('', $topicArn);
        $this->assertContains(":foo", $topicArn);
        $this->assertContains($this->instance->getZone(), $topicArn);
    }

    public function testDelete()
    {
        $topicArn = $this->instance->topics->add('foo');
        $this->assertEquals(true, $this->instance->topics->delete($topicArn));
    }

    public function testGet()
    {
        $this->instance->topics->add('foo');
        $this->instance->topics->add('bar');

        $topics = $this->instance->topics->get();
        $this->assertEquals(2, count($topics));

        foreach ($topics as $topic) {
            $this->assertTrue($this->instance->topics->delete($topic));
        }
    }

    public function testGetAttributes()
    {
        $topicArn = $this->instance->topics->add('foobar');

        $attributes = $this->instance->topics->getAttributes($topicArn);
        var_dump($attributes);

        $this->instance->topics->delete($topicArn);
    }
}