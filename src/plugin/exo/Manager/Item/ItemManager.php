<?php

namespace UJM\ExoBundle\Manager\Item;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Item\Shared;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\Definition\AnswerableItemDefinitionInterface;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Manager\Attempt\ScoreManager;
use UJM\ExoBundle\Repository\AnswerRepository;
use UJM\ExoBundle\Repository\ItemRepository;
use UJM\ExoBundle\Serializer\Item\HintSerializer;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator;

class ItemManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ScoreManager
     */
    private $scoreManager;

    /**
     * @var ItemRepository
     */
    private $repository;

    /**
     * @var ItemValidator
     */
    private $validator;

    /**
     * @var ItemSerializer
     */
    private $serializer;

    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * @var ItemDefinitionsCollection
     */
    private $itemDefinitions;

    /**
     * @var HintSerializer
     */
    private $hintSerializer;

    public function __construct(
        ObjectManager $om,
        ScoreManager $scoreManager,
        ItemValidator $validator,
        ItemSerializer $serializer,
        ItemDefinitionsCollection $itemDefinitions,
        HintSerializer $hintSerializer
    ) {
        $this->om = $om;
        $this->scoreManager = $scoreManager;
        $this->repository = $this->om->getRepository(Item::class);
        $this->answerRepository = $this->om->getRepository(Answer::class);
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->itemDefinitions = $itemDefinitions;
        $this->hintSerializer = $hintSerializer;
    }

    public function canEdit(Item $question, User $user)
    {
        $shared = $this->om->getRepository(Shared::class)
            ->findOneBy([
                'question' => $question,
                'user' => $user,
            ]);

        if (($question->getCreator() && ($question->getCreator()->getId() === $user->getId()))
            || ($shared && $shared->hasAdminRights())) {
            // User has admin rights so he can delete question
            return true;
        }

        return false;
    }

    /**
     * Validates and creates a new Item from raw data.
     *
     * @return Item
     *
     * @throws InvalidDataException
     */
    public function create(array $data)
    {
        return $this->update(new Item(), $data);
    }

    /**
     * Validates and updates a Item entity with raw data.
     *
     * @return Item
     *
     * @throws InvalidDataException
     */
    public function update(Item $question, array $data)
    {
        // Validate received data
        $validationOptions = [];
        $dataToValidate = $data;

        if ($question->hasExpectedAnswers()) {
            $validationOptions[] = Validation::REQUIRE_SOLUTIONS;
        } elseif (isset($dataToValidate['solutions'])) {
            unset($dataToValidate['solutions']);
        }
        $errors = $this->validator->validate($dataToValidate, $validationOptions);
        if (count($errors) > 0) {
            throw new InvalidDataException('Question is not valid', $errors);
        }

        // Update Item with new data
        $this->serializer->deserialize($data, $question, [Transfer::PERSIST_TAG]);

        // Save to DB
        $this->om->persist($question);
        $this->om->flush();

        return $question;
    }

    /**
     * Serializes a question.
     *
     * @return array
     */
    public function serialize(Item $question, array $options = [])
    {
        return $this->serializer->serialize($question, $options);
    }

    /**
     * Deserializes a question.
     *
     * @return Item
     */
    public function deserialize(array $itemData, Item $item = null, array $options = [])
    {
        return $this->serializer->deserialize($itemData, $item ?? new Item(), $options);
    }

    /**
     * Deletes an Item.
     * It's only possible if the Item is not used in an Exercise.
     *
     * @param $user
     * @param bool $skipErrors
     *
     * @throws \Exception
     */
    public function delete(Item $item, $user, $skipErrors = false)
    {
        if (!$this->canEdit($item, $user)) {
            if (!$skipErrors) {
                throw new \Exception('You can not delete this item.');
            } else {
                return;
            }
        }

        $this->om->remove($item);
        $this->om->flush();
    }

    /**
     * Deletes a list of Items.
     */
    public function deleteBulk(array $questions, User $user)
    {
        // Load the list of questions to delete
        $toDelete = $this->repository->findByUuids($questions);

        $this->om->startFlushSuite();
        foreach ($toDelete as $question) {
            $this->delete($question, $user, true);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Calculates the score of an answer to a question.
     *
     * @return float
     */
    public function calculateScore(Item $question, Answer $answer, bool $applyHints = true)
    {
        if ($question->hasExpectedAnswers()) {
            // Let the question correct the answer
            $definition = $this->itemDefinitions->get($question->getMimeType());
            /** @var AnswerableItemDefinitionInterface $definition */
            $corrected = $definition->correctAnswer($question->getInteraction(), json_decode($answer->getData(), true));
            if (!$corrected instanceof CorrectedAnswer) {
                $corrected = new CorrectedAnswer();
            }

            // Add hints
            if ($applyHints) {
                foreach ($answer->getUsedHints() as $hintId) {
                    // Get hint definition from question data
                    $hint = null;
                    foreach ($question->getHints() as $questionHint) {
                        if ($hintId === $questionHint->getUuid()) {
                            $hint = $questionHint;
                            break;
                        }
                    }
                    if ($hint) {
                        $corrected->addPenalty($hint);
                    }
                }
            }

            return $this->scoreManager->calculate(json_decode($question->getScoreRule(), true), $corrected);
        }

        return null;
    }

    /**
     * Calculates the total score of a question.
     *
     * @return float
     */
    public function calculateTotal(Item $question)
    {
        if ($question->hasExpectedAnswers()) {
            /** @var AnswerableItemDefinitionInterface $definition */
            $definition = $this->itemDefinitions->get($question->getMimeType());

            // Get the expected answer for the question
            $expected = $definition->expectAnswer($question->getInteraction());
            // Get all the defined answers for the question
            $all = $definition->allAnswers($question->getInteraction());

            return $this->scoreManager->calculateTotal(json_decode($question->getScoreRule(), true), $expected, $all);
        }

        return null;
    }

    /**
     * Get all scores for an Answerable Item.
     *
     * @return array
     */
    public function getItemScores(Exercise $exercise, Item $question)
    {
        $definition = $this->itemDefinitions->get($question->getMimeType());

        if ($definition instanceof AnswerableItemDefinitionInterface) {
            return array_map(function ($answer) use ($question) {
                $score = $this->calculateScore($question, $answer);

                if ($score) {
                    $total = $this->calculateTotal($question);

                    // report the score on 100
                    $score = $total > 0 ? (100 * $score) / $total : 0;
                }

                return $score;
            }, $this->answerRepository->findByQuestion($question, $exercise));
        }

        return [];
    }

    /**
     * Get question statistics inside an Exercise.
     *
     * @return array
     */
    public function getStatistics(Item $question, Exercise $exercise = null, $finishedPapersOnly = false)
    {
        $questionStats = [];

        // We load all the answers for the question (we need to get the entities as the response in DB are not processable as is)
        $answers = $this->answerRepository->findByQuestion($question, $exercise, $finishedPapersOnly);

        // Number of Users that have seen the question
        $questionStats['seen'] = count($answers);

        // Grab answer data to pass it decoded to the question type
        // it doesn't need to know the whole Answer object
        $answersData = [];

        // get corrected answers for the Item in order to compute question success percentage
        $correctedAnswers = [];

        // Number of Users that have answered the question (no blank answer)
        $questionStats['answered'] = 0;

        if (!empty($answers)) {
            // Let the handler of the question type parse and compile the data
            $definition = $this->itemDefinitions->get($question->getMimeType());

            for ($i = 0; $i < $questionStats['seen']; ++$i) {
                $answer = $answers[$i];
                if (!empty($answer->getData())) {
                    ++$questionStats['answered'];
                    $answersData[] = json_decode($answer->getData(), true);
                }

                // for each answer get corresponding correction
                if ($definition instanceof AnswerableItemDefinitionInterface && isset($answersData[$i])) {
                    $corrected = $definition->correctAnswer($question->getInteraction(), $answersData[$i]);
                    $correctedAnswers[] = $corrected;
                }
            }

            // Let the handler of the question type parse and compile the data
            if ($definition instanceof AnswerableItemDefinitionInterface) {
                $questionStats['solutions'] = $definition->getStatistics($question->getInteraction(), $answersData, $questionStats['seen']);
            }
        }

        // get the number of good answers among all
        $nbGoodAnswers = 0;

        foreach ($correctedAnswers as $corrected) {
            if ($corrected instanceof CorrectedAnswer && 0 === count($corrected->getMissing()) && 0 === count($corrected->getUnexpected())) {
                ++$nbGoodAnswers;
            }
        }
        // compute question success percentage
        $questionStats['successPercent'] = $questionStats['answered'] > 0 ? (100 * $nbGoodAnswers) / $questionStats['answered'] : 0;

        return $questionStats;
    }

    /**
     * Generates new UUIDs for the entities of an item.
     */
    public function refreshIdentifiers(Item $item)
    {
        // refresh self id
        $item->refreshUuid();

        // refresh objects ids
        foreach ($item->getObjects() as $object) {
            $object->refreshUuid();
        }

        // refresh hints ids
        foreach ($item->getHints() as $hint) {
            $hint->refreshUuid();
        }

        if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $item->getMimeType())) {
            // it's a question
            $definition = $this->itemDefinitions->get($item->getMimeType());
        } else {
            // it's a content
            $definition = $this->itemDefinitions->get(ItemType::CONTENT);
        }

        $definition->refreshIdentifiers($item->getInteraction());
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $items = $this->repository->findBy(['creator' => $from]);

        if (count($items) > 0) {
            foreach ($items as $item) {
                $item->setCreator($to);
            }

            $this->om->flush();
        }

        return count($items);
    }

    public function isQuestionType(string $type): bool
    {
        return $this->itemDefinitions->isQuestionType($type);
    }
}
