<?php

namespace  UJM\ExoBundle\Command;

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Changes the creator of questions.
 */
class JsonQuizImportCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    protected $params = [
        'file' => 'The file path: ',
        'owner' => 'The owner username: ',
        'workspace' => 'The workspace code: ',
    ];

    protected function configure()
    {
        $this->setName('claroline:json-quiz:import')->setDescription('import a json quiz');
        $this->setDefinition(
          [
            new InputArgument('file', InputArgument::REQUIRED, 'The file path'),
            new InputArgument('owner', InputArgument::REQUIRED, 'The owner username'),
            new InputArgument('workspace', InputArgument::REQUIRED, 'The workspace code'),
          ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $owner = $input->getArgument('owner');
        $workspace = $input->getArgument('workspace');

        $workspace = $this->getContainer()
            ->get('claroline.manager.workspace_manager')
            ->getOneByCode($workspace);
        $owner = $this->getContainer()
            ->get('claroline.manager.user_manager')
            ->getUserByUsername($owner);
        $json = file_get_contents($file);

        $this->getContainer()->get('ujm_exo.manager.json_quiz')->import(
            $json,
            $workspace,
            $owner
        );
    }
}
