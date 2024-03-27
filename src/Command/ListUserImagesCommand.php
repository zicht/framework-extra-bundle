<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\FrameworkExtraBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Generates an overview of all images used in the defined Entity and the defined fields.
 *
 * Results are sorted by size descending.
 */
#[AsCommand('zicht:content:list-images')]
class ListUserImagesCommand extends Command
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine, string $name = null)
    {
        $this->doctrine = $doctrine;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity to query. Example: ZichtWebsiteBundle:Page:ContentPage ')
            ->addArgument('fields', InputArgument::REQUIRED, 'The fields to check for images, comma seperated. Example: body,teaser')
            ->addOption('concat', 'c', InputOption::VALUE_OPTIONAL, 'Optional concatenation string. Example: -c "CONCAT(\'http://www.krollermuller.nl/\', p.language, \'/\', p.id)" ')
            ->setDescription('List images used in specific fields of an Entity and show their file size.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        $userFields = explode(',', $input->getArgument('fields'));
        $fields = [];

        foreach ($userFields as $fieldName) {
            $fields[$fieldName] = 'p.' . trim($fieldName);
        }

        if ('' !== $input->getOption('concat')) {
            $fields['custom'] = $input->getOption('concat') . ' as custom';
        }

        $dql = sprintf('SELECT p.id, %s FROM %s p', implode(', ', $fields), $input->getArgument('entity'));

        $qb = $em->createQuery($dql);

        if (array_key_exists('custom', $fields)) {
            unset($fields['custom']);
        }

        $list = [];

        foreach ($qb->getResult() as $record) {
            foreach (array_keys($fields) as $field) {
                if (preg_match_all('/\<img.*src=\"\/media(.*?)\".*\>/', $record[$field], $matches)) {
                    foreach ($matches[1] as $image) {
                        $imagePath = './web/media' . $image;
                        if (file_exists($imagePath)) {
                            $fileSize = filesize($imagePath);
                            $arr = [
                                'id' => $record['id'],
                                'image' => $image,
                                'size' => $fileSize !== false ? $this->humanFilesize($fileSize) : null,
                            ];

                            if ('' !== $input->getOption('concat')) {
                                $arr['custom'] = $record['custom'];
                            }

                            $list[$fileSize][] = $arr;
                        } else {
                            $io->getErrorStyle()->error('File not found ' . $image);
                        }
                    }
                }
            }
        }

        krsort($list);

        foreach ($list as $sizes) {
            foreach ($sizes as $info) {
                $io->writeln(implode(', ', $info));
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Kindly ripped from PHP.net :P.
     *
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    private function humanFilesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        /** @var 0|1|2|3|4|5 $factor */
        $factor = (int)floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}
