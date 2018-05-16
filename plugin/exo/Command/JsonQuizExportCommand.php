<?php

namespace  UJM\ExoBundle\Command;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Changes the creator of questions.
 */
class JsonQuizExportCommand extends ContainerAwareCommand
{
    use BaseCommandTrait;

    protected $params = [
        'destination' => 'The destination folder: ',
    ];

    protected function configure()
    {
        $this->setName('claroline:json-quiz:export')->setDescription('export a json quiz');
        $this->setDefinition(
            [
                new InputArgument('destination', InputArgument::REQUIRED, 'The destination folder'),
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exos = $this->getContainer()->get('claroline.api.finder')->fetch(
            'Claroline\CoreBundle\Entity\Resource\ResourceNode',
            ['resourceType' => 'ujm_exercise']
        );

        $question = new ChoiceQuestion(
          'Exercise to export',
          array_map(function ($e) {
              return "[{$e->getId()}]{$e->getName()}";
          }, $exos)
        );

        $helper = $this->getHelper('question');
        $question = $helper->ask($input, $output, $question);
        preg_match('#\[(.*)\]#', $question, $match);
        $resManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $node = $resManager->getById($match[1]);
        $exercise = $resManager->getResourceFromNode($node);
        $file = $this->getContainer()->get('ujm_exo.manager.json_quiz')->export($exercise);
        rename($file, $input->getArgument('destination').'/'.$node->getName().'.json');
    }
}
