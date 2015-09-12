<?php
namespace HendrichA\TagPassBundle;

use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractTagPass
{
    protected $container;

    protected function addCall($destinationServiceId, $method, $taggedServiceId)
    {
        if (!$this->container->has($destinationServiceId)) {
            return;
        }

        $definition = $this->container->findDefinition($destinationServiceId);
        $methodCalls = $definition->getMethodCalls();

        array_splice(
            $methodCalls,
            array_search('configure', array_map(function($call) { return $call[0]; }, $methodCalls)),
            0,
            array(array($method, array(new Reference($taggedServiceId))))
        );
        $definition->setMethodCalls($methodCalls);
    }
}
