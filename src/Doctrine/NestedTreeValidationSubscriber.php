<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Zicht\Bundle\FrameworkExtraBundle\Command\RepairNestedTreeCommand;

/**
 * This is a utility observer which validates the given repository's tree after every flush.
 *
 * You should create and enable this this subscriber only in development and/or testing environments to find out
 * whatever causes the nested set to get corrupted.
 *
 * Easiest way to use this is adding the following lines to your config_development.yml:
 *
 * <pre>
 * services:
 *      tree_validation_subscriber:
 *          class: Zicht\Bundle\FrameworkExtraBundle\Doctrine\NestedTreeValidationSubscriber
 *          arguments: ['ZichtMenuBundle:MenuItem']
 *          tags:
 *              - { name: doctrine.event_subscriber }
 * </pre>
 */
class NestedTreeValidationSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * Setup the listener to check the specified entity name after any flush
     *
     * @param string $entityName
     */
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'postFlush',
        ];
    }

    /**
     * Throw an exception if the validation fails after any save
     *
     * @return void
     * @throws \UnexpectedValueException
     */
    public function postFlush(PostFlushEventArgs $e)
    {
        $repo = $e->getEntityManager()->getRepository($this->entityName);
        if (true !== $repo->verify()) {
            throw new \UnexpectedValueException(sprintf("The repository '%s' did not validate. Run the '%s' console command to find out what's going on", $this->entityName, RepairNestedTreeCommand::COMMAND_NAME));
        }
    }
}
