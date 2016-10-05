<?php

namespace Fabiang\DoctrineDynamic;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Prophecy\Argument;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-10-05 at 13:59:46.
 *
 * @coversDefaultClass Fabiang\DoctrineDynamic\Module
 */
final class ModuleTest extends TestCase
{
    /**
     * @var Module
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Module;
    }

    /**
     * @covers ::init
     */
    public function testInit()
    {
        $sharedEventManager = $this->prophesize(
            SharedEventManagerInterface::class
        );

        $sharedEventManager->attach(
            Application::class,
            MvcEvent::EVENT_BOOTSTRAP,
            Argument::that(function (callable $callback) {
                list($obj, $method) = $callback;
                return $obj instanceof Listener\RegisterProxyDriverListener
                    && $method === 'onBootstrap';
            })
        )
        ->shouldBeCalled();

        $eventManager = $this->prophesize(EventManager::class);
        $eventManager->getSharedManager()->willReturn($sharedEventManager->reveal());

        $moduleManager = $this->prophesize(ModuleManagerInterface::class);
        $moduleManager->getEventManager()->willReturn($eventManager->reveal());

        $this->object->init($moduleManager->reveal());
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $config = $this->object->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('doctrine_dynamic', $config);
        $this->assertInternalType('array', $config['doctrine_dynamic']);

        $this->assertArrayHasKey('service_manager', $config);
        $this->assertInternalType('array', $config['service_manager']);

        $this->assertSame(
            [
                'factories' => [
                    Configuration::class => Service\ConfigurationFactory::class,
                    ProxyDriver::class   => Service\ProxyDriverFactory::class,
                ]
            ],
            $config['service_manager']
        );
    }
}
