<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Zicht\Bundle\FrameworkExtraBundle\JsonSchema\Provider\DelegatorRefProvider;

class JsonSchemaRefProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $tags = $container->findTaggedServiceIds('json_schema.remote_ref_provider');

        // The priority is optional and its value is a positive or negative integer that defaults to 0.
        // The higher the number, the earlier that observers are executed.
        uasort(
            $tags,
            function ($a, $b) {
                return ($b[0]['priority'] ?? 0) - ($a[0]['priority'] ?? 0);
            }
        );

        $definition = $container->getDefinition(DelegatorRefProvider::class);

        $definition->setArgument(
            0,
            array_map(
                function (string $id) {
                    return new Reference($id);
                },
                array_keys($tags)
            )
        );
    }
}
