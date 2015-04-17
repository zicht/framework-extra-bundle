<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use \Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks if a NestedTree set is verified (correct), if not repairs the tree
 * using the NestedSet methods.
 *
 * @package Zicht\Bundle\FrameworkExtraBundle\Command
 */
class RepairNestedTreeCommand extends ContainerAwareCommand
{
    /**
     * @{inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('zicht:repair:nested-tree')
            ->setDescription('Repair a NestedTreeSet')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to be repaired, must be of nested tree set. E.g. ZichtProjectSite:Term')
        ;
    }

    /**
     * @{inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $entity = $input->getArgument('entity');

        $repository = $doctrine
            ->getRepository($entity);
        ;

        if ($repository instanceof NestedTreeRepository) {
            if ($repository->verify() !== true) {
                $output->writeln("Errors found in tree, calling recover");
                $repository->recover();
                $doctrine->getManager()->flush();
            } else {
                $output->writeln("No issues found");
            }
        } else {
            $output->writeln("Given entity is not of instance NestedTreeRepository");
        }
    }
}