<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\UpdateSchemaDoctrineCommand;
use Symfony\Component\Console\Event\ConsoleCommandEvent;

/**
 * Class UpdateSchemaDoctrineCommandListener
 */
class UpdateSchemaDoctrineCommandListener
{
    /**
     * Disables the doctrine:schema:update-command.
     *
     * @param ConsoleCommandEvent $event
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if ($command instanceof UpdateSchemaDoctrineCommand) {
            $event->getOutput()->writeln('<error>doctrine:schema:update is disabled for this application, we use doctrine:migrations</error>');
            $event->disableCommand();
        }
    }
}
