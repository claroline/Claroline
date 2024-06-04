<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Item\Definition\AnswerableItemDefinitionInterface;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\ExerciseType;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\PaperRepository;

class ExerciseManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly ItemManager $itemManager,
        private readonly PaperManager $paperManager,
        private readonly ItemDefinitionsCollection $definitions
    ) {
    }

    /**
     * Checks if an Exercise can be deleted.
     * The exercise needs to be unpublished or have no paper to be safely removed.
     */
    public function isDeletable(Exercise $exercise): bool
    {
        return ExerciseType::CERTIFICATION !== $exercise->getType()
            || !$exercise->getResourceNode()->isPublished()
            || 0 === $this->paperManager->countExercisePapers($exercise);
    }

    public function exportPapersToCsv(Exercise $exercise)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository(Paper::class);

        $handle = fopen('php://output', 'w+');
        $limit = 250;
        $iteration = 0;
        $papers = [];

        fputcsv($handle, [
            'last_name',
            'first_name',
            'number',
            'start_date',
            'end_date',
            'status',
            'score',
            'total',
        ], ';');

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                $user = $paper->getUser();
                // maybe use stored score to speed up things
                // problem is we don't have it for non-finished papers
                $score = $this->paperManager->calculateScore($paper);

                fputcsv($handle, [
                    $user && !$paper->isAnonymized() ? $user->getLastName() : '',
                    $user && !$paper->isAnonymized() ? $user->getFirstName() : '',
                    $paper->getNumber(),
                    $paper->getStart()->format('Y-m-d H:i:s'),
                    $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : '',
                    $paper->isInterrupted() ? 'not finished' : 'finished',
                    $score !== floor($score) ? number_format($score, 2) : $score,
                    $paper->getTotal(),
                ], ';');
            }

            $this->om->clear();
        }

        fclose($handle);

        return $handle;
    }

    public function exportResultsToCsv(Exercise $exercise, $output = null)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository(Paper::class);

        $titles = [['username'], ['lastname'], ['firstname'], ['start'], ['end'], ['status'], ['score'], ['total_score_on']];
        $items = [];

        // get the list of titles for the csv (the headers)
        // this is an array of array because some question types will return...
        // more than 1 title (ie cloze)
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepQ) {
                $item = $stepQ->getQuestion();

                // only grab supported item types
                if ($this->definitions->has($item->getMimeType())) {
                    $items[$item->getUuid()] = $item;

                    /** @var AnswerableItemDefinitionInterface $definition */
                    $definition = $this->definitions->get($item->getMimeType());

                    // generate columns for item
                    $titles[$item->getUuid()] = $definition->getCsvTitles($item->getInteraction());
                }
            }
        }

        $flattenedTitles = [];

        foreach ($titles as $title) {
            foreach ($title as $subTitle) {
                $flattenedTitles[] = TextNormalizer::stripHtml($subTitle);
            }
        }

        if (null === $output) {
            $output = 'php://output';
        }
        $fp = fopen($output, 'w+');
        fputcsv($fp, [$exercise->getResourceNode()->getName()], ';');
        fputcsv($fp, $flattenedTitles, ';');

        // this is the same reason why we use an array of array here
        $limit = 250;
        $iteration = 0;
        $papers = [];

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;
            $dataPapers = [];

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                // maybe use stored score to speed up things
                // problem is we don't have it for non-finished papers
                $score = $this->paperManager->calculateScore($paper);

                $answers = $paper->getAnswers();
                $csv = [];
                $user = $paper->getUser();

                if ($user) {
                    $csv['username'] = [$paper->isAnonymized() ? '' : $user->getUsername()];
                    $csv['lastname'] = [$paper->isAnonymized() ? '' : $user->getLastName()];
                    $csv['firstname'] = [$paper->isAnonymized() ? '' : $user->getFirstName()];
                } else {
                    $csv['username'] = ['none'];
                    $csv['lastname'] = ['none'];
                    $csv['firstname'] = ['none'];
                }

                $csv['start'] = [$paper->getStart()->format('Y-m-d H:i:s')];
                $csv['end'] = [$paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : ''];
                $csv['status'] = [$paper->isInterrupted() ? 'not finished' : 'finished'];
                $csv['score'] = [$score !== floor($score) ? number_format($score, 2) : $score];
                $csv['total_score_on'] = [$paper->getTotal()];

                foreach ($items as $item) {
                    /** @var AnswerableItemDefinitionInterface $itemDefinition */
                    $itemDefinition = $this->definitions->get($item->getMimeType());

                    $found = false;
                    // get question parameters from paper
                    $itemData = $paper->getQuestion($item->getUuid());
                    if (!empty($itemData)) {
                        // get item entities
                        $paperItem = $this->itemManager->deserialize($itemData, null, [Transfer::NO_FETCH]);

                        foreach ($answers as $answer) {
                            if ($answer->getQuestionId() === $item->getUuid()) {
                                $found = true;
                                $csv[$answer->getQuestionId()] = $itemDefinition->getCsvAnswers($paperItem->getInteraction(), $answer);
                            }
                        }
                    }

                    // question has no answer, we need to add placeholders
                    if (!$found) {
                        $countBlank = count($itemDefinition->getCsvTitles($item->getInteraction()));
                        $csv[$item->getUuid()] = array_pad([], $countBlank, '');
                    }
                }

                $dataPapers[] = $csv;
            }

            $flattenedData = [];

            foreach ($dataPapers as $paper) {
                $flattenedAnswers = [];

                foreach ($paper as $paperItem) {
                    if (is_array($paperItem)) {
                        foreach ($paperItem as $paperEl) {
                            $flattenedAnswers[] = $paperEl ? TextNormalizer::stripHtml($paperEl, true) : null;
                        }
                    }
                }
                $flattenedData[] = $flattenedAnswers;
            }

            $this->om->clear();

            foreach ($flattenedData as $item) {
                fputcsv($fp, $item, ';');
            }
        }

        fclose($fp);

        return $fp;
    }
}
