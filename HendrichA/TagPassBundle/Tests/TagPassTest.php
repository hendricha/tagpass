<?php
namespace HendrichA\TagPassLibrary\Tests;

use HendrichA\TagPassLibrary\TagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TagPassTest extends \PHPUnit_Framework_TestCase
{
    public function testTagPassShouldAddMethodCallsToConfiguredTarget()
    {
        $container = $this->createContainer();
        $tagPass = new TagPass('foo');
        $tagPass->addServiceIdsTo('mock_service', 'addFoo');

        $tagPass->process($container);

        $definition = $container->getDefinition('mock_service');
        $this->assertEquals(count($definition->getMethodCalls()), 1);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addFoo', array(new Reference('mock_extension_Service')))
        ));

        $definition = $container->getDefinition('non_relevant_service');
        $this->assertEquals(count($definition->getMethodCalls()), 0);
    }

    private function createContainer()
    {
        $container = new ContainerBuilder();
        $container
            ->register('mock_service');
        $container
            ->register('mock_extension_Service')
            ->addTag('foo');
        $container
            ->register('non_relevant_service');

        return $container;
    }

    public function testTagPassShouldBeAbleToAddMethodCallsToMultipleTargets()
    {
        $container = $this->createContainer();
        $container
            ->register('mock_service2');
        $tagPass = new TagPass('foo');
        $tagPass->addServiceIdsTo('mock_service', 'addFoo');
        $tagPass->addServiceIdsTo('mock_service2', 'addBar');

        $tagPass->process($container);

        $definition = $container->getDefinition('mock_service');
        $this->assertEquals(count($definition->getMethodCalls()), 1);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addFoo', array(new Reference('mock_extension_Service')))
        ));
        $definition = $container->getDefinition('mock_service2');
        $this->assertEquals(count($definition->getMethodCalls()), 1);
        $this->assertEquals($definition->getMethodCalls(), array(
            array('addBar', array(new Reference('mock_extension_Service')))
        ));

        $definition = $container->getDefinition('non_relevant_service');
        $this->assertEquals(count($definition->getMethodCalls()), 0);
    }

    public function testTagPassShouldAddMethodCallsBeforeConfigureCall()
    {
        $container = $this->createContainer();
        $container->getDefinition('mock_service')->addMethodCall('configure');

        $tagPass = new TagPass('foo');
        $tagPass->addServiceIdsTo('mock_service', 'addFoo');

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
