<?php

namespace UJM\ExoBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Manager\Attempt\PaperManager;

/**
 * Recomputes score for quiz papers.
 */
class ComputeScoresCommand extends Command
{
    private $om;
    private $paperManager;

    public function __construct(ObjectManager $om, PaperManager $paperManager)
    {
        $this->om = $om;
        $this->paperManager = $paperManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Recomputes papers scores for a quiz.')
            ->setDefinition([
                new InputArgument('quiz_id', InputArgument::REQUIRED, 'The resource node ID of the quiz.'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('quiz_id');

        /** @var Exercise $quiz */
        $quiz = $this->om->getRepository(Exercise::class)->findOneBy(['resourceNode' => $id]);
        /** @var Paper[] $papers */
        $papers = $this->om->getRepository(Paper::class)->findBy(['exercise' => $quiz]);

        $output->writeln(sprintf('Found %d papers to compute', count($papers)));

        foreach ($papers as $paper) {
            $output->writeln(sprintf('- Processing paper : %s', $paper->getUuid()));

            $paper->setTotal($this->paperManager->calculateTotal($paper));
            $paper->setScore($this->paperManager->calculateScore($paper));

            $this->om->persist($paper);
        }

        $this->om->flush();
    }
}
