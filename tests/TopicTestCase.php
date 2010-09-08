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
class TopicTestCase extends AbstractTestCase
{
    protected $topicPrefix = 'SASNS_';

    public function testSet()
    {
        $topics = $this->instance->topics;
        $this->assertType('Services_Amazon_SNS_Topics', $topics);
    }

    public function testCreate()
    {
        $name = "{$this->topicPrefix}foo";

        $topics   = $this->instance->topics;
        $topicArn = $topics->add($name);
        $this->assertNotEquals('', $topicArn);
        $this->assertContains(":{$name}", $topicArn);
        $this->assertContains($this->instance->getZone(), $topicArn);
    }

    public function testDelete()
    {
        $name = "{$this->topicPrefix}foo";

        $topicArn = $this->instance->topics->add($name);
        $this->assertEquals(true, $this->instance->topics->delete($topicArn));
    }

    /**
     * This test might fail when SNS doesn't return the two topics yet.
     *
     * @return void
     */
    public function testGet()
    {
        $this->instance->topics->add("{$this->topicPrefix}foo");
        $this->instance->topics->add("{$this->topicPrefix}bar");

        sleep(10);

        $topics = $this->instance->topics->get();
        $this->assertEquals(2, count($topics), "We were probably too fast. Re-run this test, please.");

        foreach ($topics as $topic) {
            $this->assertTrue($this->instance->topics->delete($topic));
        }
    }

    /**
     * A new topic should return this set of attributes.
     *
     * @return void
     */
    public function testGetAttributes()
    {
        $topicArn = $this->instance->topics->add("{$this->topicPrefix}foobar");

        $attributes = $this->instance->topics->getAttributes($topicArn);

        $this->assertTrue(isset($attributes['Owner']));
        $this->assertTrue(isset($attributes['SubscriptionsPending']));
        $this->assertTrue(isset($attributes['Policy']));
        $this->assertTrue(isset($attributes['SubscriptionsConfirmed']));
        $this->assertTrue(isset($attributes['SubscriptionsDeleted']));
        $this->assertTrue(isset($attributes['TopicArn']));

        $this->assertEquals($topicArn, $attributes['TopicArn']);

        $this->instance->topics->delete($topicArn);
    }

    /**
     * Set an attribute.
     *
     * @return void
     */
    public function testSetAttribute()
    {
        $topicArn = $this->instance->topics->add("{$this->topicPrefix}fubar");

        $displayName = 'FUBAR';

        $this->assertTrue($this->instance->topics->setAttribute(
            $topicArn,
            'DisplayName',
            $displayName
        ));

        $attributes = $this->instance->topics->getAttributes($topicArn);

        $this->assertEquals($displayName, $attributes['DisplayName']);

        $this->instance->topics->delete($topicArn);
    }

    public function testGetPermissions()
    {
        $topicArn = $this->instance->topics->add("{$this->topicPrefix}WADDAP");

        $permissions = $this->instance->topics->getPermissions($topicArn);
        $this->assertTrue(is_array($permissions));
        $this->assertEquals(1, count($permissions));

        $this->instance->topics->delete($topicArn);
    }

    /**
     * Full flexed permission test.
     *
     * @return void
     */
    public function testPermissions()
    {
        $this->fail('To be implemented.');

        $topicArn = $this->instance->topics->add("{$this->topicPrefix}PERM");

        $label = 'ServicesAmazonSNSPermTest';

        $this->instance->getPermissions($topicArn);

        $this->instance->addPermission(
            $topicArn,
            $label,
            array('aws' => 'action', 'aws2' => 'action')
        );

        $this->instance->deletePermission($topicArn, $label);
    }
}