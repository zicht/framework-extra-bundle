<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;

/**
 * Class ValidateEntityCommand
 * @package Zicht\Bundle\FrameworkExtraBundle\Command
 */
class ValidateEntityCommand extends ContainerAwareCommand
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:entity:validate')
            ->addArgument('entity', InputArgument::IS_ARRAY|InputArgument::OPTIONAL)
            ->setHelp('This command validates all entities in a repository, useful to test the database for irregularities')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Optional validation group(s)")
        ;
    }



    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entities = $input->getArgument('entity') ?: $this->getAllEntities();

        foreach($entities as $entity) {
            $groups = $input->getOption('group');
            $repo = $this->getContainer()->get('doctrine')->getRepository($entity);

            foreach ($repo->findAll() as $entity) {
                $violations = $this->getContainer()->get('validator')->validate($entity, $groups ? $groups : null);

                if (count($violations)) {
                    $output->writeln(get_class($entity) . "::" . $entity->getId());
                    foreach ($violations as $error) {
                        $output->writeln(" -> {$error}");
                    }
                }
            }
        }
    }

    protected function getAllEntities()
    {
        $allMeta = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getMetadataFactory()
            ->getAllMetadata();

        $isAcceptable = function ($meta) {
            return !$meta->isMappedSuperclass && empty($meta->subClasses);
        };

        return \Zicht\Itertools\filter($isAcceptable, $allMeta);
    }
}