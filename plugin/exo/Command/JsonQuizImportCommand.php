<?php

namespace  UJM\ExoBundle\Command;

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UJM\ExoBundle\Library\Options\Validation;

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
        $data = json_decode(file_get_contents($file));

        //validation
        $validator = $this->getContainer()->get('ujm_exo.validator.exercise');
        $errors = $validator->validate($data, [Validation::REQUIRE_SOLUTIONS]);

        if ($errors) {
            $output->writeln('<error>Errors were found in the json schema:</error>');
            $output->writeln('<error>'.json_encode($errors).'</error>');

            return;
        }

        $this->getContainer()->get('ujm_exo.manager.json_quiz')->import(
            $data,
            $workspace,
            $owner
        );
    }
}
