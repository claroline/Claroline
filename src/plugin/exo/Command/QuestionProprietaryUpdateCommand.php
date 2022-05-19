<?php

namespace UJM\ExoBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use UJM\ExoBundle\Entity\Item\Item;

/**
 * Changes the creator of questions.
 */
class QuestionProprietaryUpdateCommand extends Command
{
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('new_owner');
        $id = $input->getArgument('question_id');
        $helper = $this->getHelper('question');
        $newOwner = $this->om->getRepository(User::class)->loadUserByUsername($username);
        $item = $this->om->getRepository(Item::class)->find($id);
        $all = $input->getOption('all');

        $items = $all ?
           $this->om->getRepository(Item::class)->findBy([
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

        return 0;
    }
}
