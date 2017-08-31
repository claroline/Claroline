<?php

namespace  UJM\ExoBundle\Command;

use Claroline\CoreBundle\Command\Traits\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UJM\ExoBundle\Library\Options\Validation;

/**
 * Changes the creator of questions.
 */
class JsonQuizImportCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;
    const BATCH_SIZE = 5;

    protected $params = [
        'file' => 'The file (or directory) path: ',
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

        $this->addOption(
            'dry_run',
            'd',
            InputOption::VALUE_NONE,
            'When set to true, remove groups from the workspace'
        );

        $this->addOption(
            'show_error_schema',
            'o',
            InputOption::VALUE_NONE,
            'When set to true, remove groups from the workspace'
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
        $data = [];
        $om = $this->getContainer()->get('claroline.persistence.object_manager');

        if (is_file($file)) {
            $data[$file] = json_decode(file_get_contents($file));
        }

        if (is_dir($file)) {
            $iterator = new \DirectoryIterator($file);

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $data[$file->getPathname()] = json_decode(file_get_contents($file->getPathname()));
                }
            }
        }

        $i = 0;
        $count = count($data);
        $output->writeln("Start the import: {$i}/{$count}");

        foreach ($data as $path => $question) {
            //validation
            ++$i;
            $validator = $this->getContainer()->get('ujm_exo.validator.exercise');
            $errors = $validator->validate($question, [Validation::REQUIRE_SOLUTIONS]);
            if (!$input->getOption('dry_run') && !$errors) {
                $output->writeln("Importing {$path}: {$i}/{$count}");
            }

            if ($errors) {
                $output->writeln('<error>Errors were found in the json schema for :'.$path.'</error>');
                if ($input->getOption('show_error_schema')) {
                    $output->writeln('<error>'.json_encode($errors).'</error>');
                }
            }

            if (!$input->getOption('dry_run') && !$errors) {
                $this->getContainer()->get('ujm_exo.manager.json_quiz')->import(
                $question,
                $workspace,
                $owner
              );
            }

            if ($i % self::BATCH_SIZE === 0) {
                $om->clear();
                $file = $input->getArgument('file');
                $owner = $input->getArgument('owner');
                $workspace = $input->getArgument('workspace');

                $workspace = $this->getContainer()
                  ->get('claroline.manager.workspace_manager')
                  ->getOneByCode($workspace);
                $owner = $this->getContainer()
                  ->get('claroline.manager.user_manager')
                  ->getUserByUsername($owner);
            }
        }
    }
}
