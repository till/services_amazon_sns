<?php
class BaseTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var array $config The config array.
     */
    protected $config;

    /**
     * @var Services_Amazon_SNS $instance
     */
    protected $instance;

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

    public function testCredentials()
    {
        $this->assertEquals($this->config['accessKeyId'], $this->instance->getAccessKeyId());
        $this->assertEquals($this->config['secretAccessKey'], $this->instance->getSecretAccessKey());
    }

    public function testZone()
    {
        $this->assertEquals('us-east-1', $this->instance->getZone());
    }
}