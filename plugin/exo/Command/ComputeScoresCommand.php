<?php

namespace UJM\ExoBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\Attempt\PaperManager;

/**
 * Recomputes score for quiz papers.
 */
class ComputeScoresCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:quiz:scores')
            ->setDescription('Recomputes papers scores for a quiz.')
            ->setDefinition([
                new InputArgument('quiz_id', InputArgument::REQUIRED, 'The resource node ID of the quiz.'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ObjectManager $om */
        $om = $this->getContainer()->get(ObjectManager::class);
        /** @var PaperManager $manager */
        $manager = $this->getContainer()->get(PaperManager::class);

        $id = $input->getArgument('quiz_id');

        /** @var Exercise $quiz */
        $quiz = $om->getRepository(Exercise::class)->findOneBy(['resourceNode' => $id]);
        /** @var Paper[] $papers */
        $papers = $om->getRepository(Paper::class)->findBy(['exercise' => $quiz]);

        $output->writeln(sprintf('Found %d papers to compute', count($papers)));

        foreach ($papers as $paper) {
            $output->writeln(sprintf('- Processing paper : %s', $paper->getUuid()));

            $paper->setTotal($manager->calculateTotal($paper));
            $paper->setScore($manager->calculateScore($paper));

            $om->persist($paper);
        }

        $om->flush();
    }
}
