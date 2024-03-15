<?php declare(strict_types=1);

namespace Zicht\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\FrameworkExtraBundle\EventListener\SearchBotBlockerRequestListener;

/**
 * Tags the {@see SearchBotBlockerRequestListener} with the kernel.event_listener listener tag to listen to the
 * kernel.request event when the `BLOCK_SEARCH_BOTS` env var flag is true.
 */
class ActivateSearchBotBlockerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SearchBotBlockerRequestListener::class)) {
            return;
        }

        $doBlockSearchBots = $container->resolveEnvPlaceholders($container->getParameter('zicht_framework_extra.block_search_bots'), true);
        $doBlockSearchBots = filter_var($doBlockSearchBots, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
        if ($doBlockSearchBots !== true) {
            return;
        }

        $definition = $container->getDefinition(SearchBotBlockerRequestListener::class);
        $definition->addTag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequestBlockSearchBots', 'priority' => pow(2, 13)]);

        $searchBotsListFile = dirname(__DIR__, 2) . '/Resources/config/search_bots.list';
        if (is_readable($searchBotsListFile)) {
            $searchBotsListPatterns = array_filter(
                file($searchBotsListFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [],
                static fn (string $line): bool => strpos($line, '#') !== 0
            );
            if (count($searchBotsListPatterns) > 0) {
                $definition->addMethodCall('setSearchBotsListPatterns', [$searchBotsListPatterns]);
            }
        }
    }
}
