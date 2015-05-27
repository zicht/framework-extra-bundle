<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;

class ValidateEntityCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zicht:entity:validate')
            ->addArgument('entity')
            ->setHelp('This command validates all entities in a repository, useful to test the database for irregularities')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Optional validation group(s)")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groups = $input->getOption('group');
        $repo = $this->getContainer()->get('doctrine')->getRepository($input->getArgument('entity'));

        foreach ($repo->findAll() as $page) {
            $violations = $this->getContainer()->get('validator')->validate($page, $groups ? $groups : null);

            if (count($violations)) {
                $output->writeln(get_class($page) . "::" . $page->getId());
                foreach ($violations as $error) {
                    $output->writeln(" -> {$error}");
                }
            }
        }
    }
}