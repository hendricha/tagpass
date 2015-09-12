<?php
namespace HendrichA\TagPassBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TargetedTagPass extends AbstractTagPass implements CompilerPassInterface
{
    protected $method;

    public function __construct($tag, $method)
    {
        $this->tag = $tag;
        $this->method = $method;
    }

    public function process(ContainerBuilder $container)
    {
        $this->container = $container;

        foreach ($container->findTaggedServiceIds($this->tag) as $taggedServiceId => $tags) {
            foreach ($tags as $attributes) {
                $this->processAttributes($attributes, $taggedServiceId);
            }
       }
    }

    private function processAttributes($attributes, $taggedServiceId)
    {
        if (isset($attributes['service']) && isset($attributes['tag'])) {
            throw new \LogicException('Tagged service (' . $taggedServiceId . ') can only contain either a service or a tag, in tag "' . $this->tag . '"');
        }

        if (isset($attributes['service'])) {
            return $this->addCall($attributes['service'], $this->method, $taggedServiceId);
        }

        if (isset($attributes['tag'])) {
            foreach ($this->container->findTaggedServiceIds($attributes['tag']) as $targetServiceId => $tags) {
                $this->addCall($targetServiceId, $this->method, $taggedServiceId);
            }

            return;
        }

        throw new \LogicException('Tagged service (' . $taggedServiceId . ') should contain at least a service or a tag in  tag "' . $this->tag . '"');
    }
}
