<?php
namespace HendrichA\TagPassLibrary\Tests;

use HendrichA\TagPassLibrary\TargetedTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TargetedTagPassTest extends \PHPUnit_Framework_TestCase
{
    public function testTargetedTagPassShouldAddMethodCallsToConfiguredTarget()
    {
        $container = $this->createContainer(array('service' => 'mock_service'));
        $tagPass = new TargetedTagPass('foo', 'addFoo');

        $tagPass->process($container);

        $definition = $container->getDefinition('mock_service');
        $this->assertEquals(count($definition->getMethodCalls()), 1);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addFoo', array(new Reference('mock_extension_Service')))
        ));

        $definition = $container->getDefinition('non_relevant_service');
        $this->assertEquals(count($definition->getMethodCalls()), 0);
    }

    private function createContainer($tagConfig)
    {
        $container = new ContainerBuilder();
        $container
            ->register('mock_service');
        $container
            ->register('non_relevant_service');
        $container
            ->register('mock_extension_Service')
            ->addTag('foo', $tagConfig);

        return $container;
    }

    public function testTargetedTagPassShouldAddMethodCallsToConfiguredTargetByTag()
    {
        $container = $this->createContainer(array('tag' => 'foo_target'));
        $container->getDefinition('mock_service')->addTag('foo_target');
        $container->register('mock_service2')->addTag('foo_target');

        $tagPass = new TargetedTagPass('foo', 'addFoo');

        $tagPass->process($container);

        $definition = $container->getDefinition('mock_service');
        $this->assertEquals(count($definition->getMethodCalls()), 1);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addFoo', array(new Reference('mock_extension_Service')))
        ));

        $definition = $container->getDefinition('mock_service2');
        $this->assertEquals(count($definition->getMethodCalls()), 1);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addFoo', array(new Reference('mock_extension_Service')))
        ));

        $definition = $container->getDefinition('non_relevant_service');
        $this->assertEquals(count($definition->getMethodCalls()), 0);
    }

    public function testTargetedTagPassShouldAddMethodCallsBeforeConfigureCall()
    {
        $container = $this->createContainer(array('service' => 'mock_service'));
        $container->getDefinition('mock_service')->addMethodCall('configure');

        $tagPass = new TargetedTagPass('foo', 'addFoo');

        $tagPass->process($container);

        $definition = $container->getDefinition('mock_service');
        $this->assertEquals(count($definition->getMethodCalls()), 2);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addFoo', array(new Reference('mock_extension_Service'))),
            array('configure', array())
        ));

        $definition = $container->getDefinition('non_relevant_service');
        $this->assertEquals(count($definition->getMethodCalls()), 0);
    }
}
