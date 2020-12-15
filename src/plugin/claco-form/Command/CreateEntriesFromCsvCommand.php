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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Manager\ClacoFormManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateEntriesFromCsvCommand extends Command
{
    use BaseCommandTrait;

    private $om;
    private $resourceManager;
    private $formManager;

    private $params = [
        'username' => 'The username of the creator ',
        'clacoform_node_id' => 'The uuid of the ClacoForm resource node ',
        'csv_path' => 'Absolute path to the csv file ',
    ];

    public function __construct(ObjectManager $om, ResourceManager $resourceManager, ClacoFormManager $formManager)
    {
        $this->om = $om;
        $this->resourceManager = $resourceManager;
        $this->formManager = $formManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create entries for a ClacoForm from a csv file');
        $this->setDefinition([
            new InputArgument('username', InputArgument::REQUIRED, 'The username of the creator'),
            new InputArgument('clacoform_node_id', InputArgument::REQUIRED, 'The uuid of the ClacoForm resource node'),
            new InputArgument('csv_path', InputArgument::REQUIRED, 'The absolute path to the csv file'),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resourceManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $userRepo = $this->om->getRepository(User::class);
        $resourceNodeRepo = $this->om->getRepository(ResourceNode::class);

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

            return 1;
        }
        if (!$clacoForm) {
            $output->writeln("<error>Coudn't find ClacoForm resource.</error>");

            return 1;
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
            $manager = $this->getContainer()->get('Claroline\ClacoFormBundle\Manager\ClacoFormManager');
            $manager->importEntryFromCsv($clacoForm, $user, $data);

            return 0;
        }
        $output->writeln('<error>CSV file must contain more the 1 line.</error>');

        return 1;
    }
}
