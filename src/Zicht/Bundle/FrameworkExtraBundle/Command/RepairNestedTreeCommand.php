<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Checks if a NestedTree set is verified (correct), if not repairs the tree
 * using the NestedSet methods.
 */
class RepairNestedTreeCommand extends ContainerAwareCommand
{
    /**
     * This is const because it's referred by the exception thrown when a validation error occurs
     */
    const COMMAND_NAME = 'zicht:repair:nested-tree';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Repair a NestedTreeSet')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Do a dry run, i.e. only report the problems without doing any changes')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to be repaired, must be of nested tree set. E.g. ZichtMenuBundle:MenuItem')
            ->setHelp(
                'This command will try to repair broken nested set.' . PHP_EOL .
                'Pass --dry-run to see if the nested set is broken (use --verbose to see which items are broken)' . PHP_EOL
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $doctrine = $this->getContainer()->get('doctrine');
        $entity = $input->getArgument('entity');
        $formatter = $this->getHelperSet()->get('formatter');
        $repository = $doctrine->getRepository($entity);

        if ($repository instanceof NestedTreeRepository) {
            if (true !== ($issues = $repository->verify())) {
                $io->getErrorStyle()->error('Errors found in tree');
                $io->getErrorStyle()->error($formatter->formatBlock($issues, 'comment', true));
                if ($input->getOption('dry-run')) {
                    $io->writeln('Dry run, so no changes are made');
                } else {
                    $io->writeln('Recovering');
                    $repository->recover();
                    $doctrine->getManager()->flush();
                }
            } else {
                $io->writeln('No issues found');
            }
        } else {
            $io->getErrorStyle()->error('Given entity is not of instance NestedTreeRepository');
        }
    }
}
