<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Manager\Attempt\AnswerManager;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;

class CorrectionManager
{
    private PaperRepository $paperRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly AnswerManager $answerManager,
        private readonly PaperManager $paperManager,
        private readonly ItemSerializer $itemSerializer
    ) {
        $this->paperRepository = $this->om->getRepository(Paper::class);
    }

    public function getToCorrect(Exercise $exercise): array
    {
        $answers = [];
        $questions = [];

        // Load papers that have non noted questions
        $papers = $this->paperRepository->findPapersToCorrect($exercise);

        foreach ($papers as $paper) {
            /** @var Answer $answer */
            foreach ($paper->getAnswers() as $answer) {
                if (null === $answer->getScore()) {
                    $answers[] = $this->answerManager->serialize($answer, ['hints' => $paper->getHints()]);

                    // For now we only get one definition for a question
                    // As the papers are ordered by start date DESC, we will get the most recent version of the question
                    $questions[$answer->getQuestionId()] = $paper->getQuestion($answer->getQuestionId());
                }
            }
        }

        return [
            'questions' => array_values($questions),
            'answers' => $answers,
        ];
    }

    /**
     * Save scores and feedback for questions.
     *
     * @throws InvalidDataException
     */
    public function save(array $correctedAnswers = []): void
    {
        $updatedPapers = [];

        foreach ($correctedAnswers as $index => $correctedAnswer) {
            /** @var Answer $answer */
            $answer = $this->om->getRepository(Answer::class)->findOneBy([
                'uuid' => $correctedAnswer['id'],
            ]);

            if (empty($answer)) {
                throw new InvalidDataException('Submitted answers are invalid', [['path' => "/{$index}", 'message' => 'answer does not exists']]);
            }

            $question = $answer->getPaper()->getQuestion($answer->getQuestionId());
            $decodedQuestion = $this->itemSerializer->deserialize($question, new Item());

            // Update answer and apply hint penalties
            $this->answerManager->update($decodedQuestion, $answer, $correctedAnswer, true);
            if (!empty($answer->getUsedHints())) {
                $this->applyPenalties($answer);
            }

            $updatedPapers[$answer->getPaper()->getId()] = $answer->getPaper();
        }

        // A first flush is needed because score calculation for the whole paper retrieve scores from DB
        $this->om->flush();

        // Recalculate scores for updated papers
        foreach ($updatedPapers as $paper) {
            $newScore = $this->paperManager->calculateScore($paper);
            $paper->setScore($newScore);
            $this->om->persist($paper);
        }

        $this->om->flush();
    }

    private function applyPenalties(Answer $answer): void
    {
        $paper = $answer->getPaper();

        // Retrieve the def of the question which is answered
        $question = $paper->getQuestion($answer->getQuestionId());

        foreach ($answer->getUsedHints() as $usedHint) {
            foreach ($question['hints'] as $hint) {
                if ($usedHint === $hint['id'] && 0 !== $hint['penalty']) {
                    $answer->setScore($answer->getScore() - $hint['penalty']);
                }
            }
        }
    }
}
