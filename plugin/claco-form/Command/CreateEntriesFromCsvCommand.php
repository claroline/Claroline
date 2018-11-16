<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ClacoFormBundle\Command;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEntriesFromCsvCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    private $params = [
        'username' => 'The username of the creator ',
        'clacoform_node_id' => 'The uuid of the ClacoForm resource node ',
        'csv_path' => 'Absolute path to the csv file ',
    ];

    protected function configure()
    {
        $this->setName('claroline:csv:clacoform')
            ->setDescription('Create entries for a ClacoForm from a csv file');
        $this->setDefinition([
            new InputArgument('username', InputArgument::REQUIRED, 'The username of the creator'),
            new InputArgument('clacoform_node_id', InputArgument::REQUIRED, 'The uuid of the ClacoForm resource node'),
            new InputArgument('csv_path', InputArgument::REQUIRED, 'The absolute path to the csv file'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $resourceManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $userRepo = $om->getRepository(User::class);
        $resourceNodeRepo = $om->getRepository(ResourceNode::class);

        $file = $input->getArgument('csv_path');
        $content = str_replace(PHP_EOL, '<br/>', file_get_contents($file));
        $lines = explode(';"<endofline>"<br/>', $content);

        $username = $input->getArgument('username');
        $user = $userRepo->findOneBy(['username' => $username]);

        $nodeId = $input->getArgument('clacoform_node_id');
        $node = $resourceNodeRepo->findOneBy(['uuid' => $nodeId]);
        $clacoForm = $node ? $resourceManager->getResourceFromNode($node) : null;

        if (!$user) {
            $output->writeln("<error>Coudn't find user.</error>");

            return;
        }
        if (!$clacoForm) {
            $output->writeln("<error>Coudn't find ClacoForm resource.</error>");

            return;
        }
        if (1 < count($lines)) {
            $data = [];
            $keys = str_getcsv($lines[0], ';');

            foreach ($lines as $index => $line) {
                if ($index > 0) {
                    $lineNum = $index + 1;
                    $lineData = [];
                    $lineArray = str_getcsv($line, ';');

                    if (count($lineArray) > count($keys)) {
                        throw new \Exception("Line {$lineNum} has too many args.");
                    }

                    foreach ($lineArray as $key => $value) {
                        $lineData[$keys[$key]] = $value;
                    }
                    $data[] = $lineData;
                }
            }
            $manager = $this->getContainer()->get('claroline.manager.claco_form_manager');
            $manager->setLogger($consoleLogger);
            $manager->importEntryFromCsv($clacoForm, $user, $data);
        } else {
            $output->writeln('<error>CSV file must contain more the 1 line.</error>');
        }
    }
}
