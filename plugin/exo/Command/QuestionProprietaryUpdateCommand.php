<?php

namespace  UJM\ExoBundle\Command;

use Claroline\AppBundle\Command\BaseCommandTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Changes the creator of questions.
 */
class QuestionProprietaryUpdateCommand extends Command
{
    use BaseCommandTrait;

    protected $params = [
        'new_owner' => 'The new owner username: ',
        'question_id' => 'The question id: ',
    ];

    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Update a question owner');
        $this->setDefinition(
            [
                new InputArgument('new_owner', InputArgument::REQUIRED, 'The new owner username'),
                new InputArgument('question_id', InputArgument::REQUIRED, 'The question id'),
            ]
        );
        $this->addOption(
            'all',
            'a',
            InputOption::VALUE_NONE,
            'When set to true, all question of the previous users will be updated'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('new_owner');
        $id = $input->getArgument('question_id');
        $helper = $this->getHelper('question');
        $newOwner = $this->om->getRepository('ClarolineCoreBundle:User')->loadUserByUsername($username);
        $item = $this->om->getRepository('UJMExoBundle:Item\Item')->find($id);
        $all = $input->getOption('all');

        $items = $all ?
           $this->om->getRepository('UJMExoBundle:Item\Item')->findBy([
               'creator' => $item->getCreator(),
           ]) :
           [$item];

        $output->writeln('Questions found:');

        foreach ($items as $item) {
            $output->writeln("{$item->getTitle()} - {$item->getDescription()} - {$item->getUser()}");
        }

        $item = new ConfirmationQuestion('Do you want to update these questions ? y/n [y] ', true);

        if ($helper->ask($input, $output, $item)) {
            foreach ($items as $item) {
                $item->setUser($newOwner);
                $this->om->persist($item);
            }
        }

        $output->writeln('Flushing...');
        $this->om->flush();
    }
}
