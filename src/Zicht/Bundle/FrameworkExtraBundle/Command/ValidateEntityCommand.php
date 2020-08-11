<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ValidateEntityCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:entity:validate')
            ->addArgument('entity', InputArgument::IS_ARRAY | InputArgument::OPTIONAL)
            ->setHelp('This command validates all entities in a repository, useful to test the database for irregularities')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Optional validation group(s)');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $entities = $input->getArgument('entity') ?: $this->getAllEntities();

        foreach ($entities as $entity) {
            $groups = $input->getOption('group');
            $repo = $this->getContainer()->get('doctrine')->getRepository($entity);

            foreach ($repo->findAll() as $entity) {
                $violations = $this->getContainer()->get('validator')->validate($entity, $groups ? $groups : null);

                if (count($violations)) {
                    $io->getErrorStyle()->writeln(get_class($entity) . '::' . $entity->getId());
                    foreach ($violations as $error) {
                        $io->getErrorStyle()->writeln(" -> {$error}");
                    }
                }
            }
        }
    }

    /**
     * @return \Generator
     */
    protected function getAllEntities()
    {
        $allMeta = $this
            ->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getMetadataFactory()
            ->getAllMetadata();

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $meta */
        foreach ($allMeta as $meta) {
            if (!$meta->isMappedSuperclass && empty($meta->subClasses)) {
                yield $meta->getName();
            }
        }
    }
}
