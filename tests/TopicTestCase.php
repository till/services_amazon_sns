<?php
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
    }

    public function testGet()
    {
    }
}