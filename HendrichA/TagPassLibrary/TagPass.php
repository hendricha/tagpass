<?php
namespace HendrichA\TagPassLibrary;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TagPass extends AbstractTagPass implements CompilerPassInterface
{
    private $tag;
    private $destinations = array();
    private $serviceName;
    private $method;

    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    public function addServiceIdsTo($serviceId, $method)
    {
        $this->destinations[] = array(
            'id' => $serviceId,
            'method' => $method
        );

        return $this;
    }

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        foreach ($container->findTaggedServiceIds($this->tag) as $taggedServiceId => $tags) {
           foreach($this->destinations as $destination) {
               $this->addCall($destination['id'], $destination['method'], $taggedServiceId);
           }
       }
    }
}
