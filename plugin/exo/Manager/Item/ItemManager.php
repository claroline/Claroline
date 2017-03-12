<?php

namespace UJM\ExoBundle\Manager\Item;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Attempt\ScoreManager;
use UJM\ExoBundle\Repository\AnswerRepository;
use UJM\ExoBundle\Repository\ItemRepository;
use UJM\ExoBundle\Serializer\Item\HintSerializer;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Item\ItemValidator;

/**
 * @DI\Service("ujm_exo.manager.item")
 */
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

    /**
     * ItemManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "scoreManager"    = @DI\Inject("ujm_exo.manager.score"),
     *     "validator"       = @DI\Inject("ujm_exo.validator.item"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.item"),
     *     "itemDefinitions" = @DI\Inject("ujm_exo.collection.item_definitions"),
     *     "hintSerializer"  = @DI\Inject("ujm_exo.serializer.hint")
     * })
     *
     * @param ObjectManager             $om
     * @param ScoreManager              $scoreManager
     * @param ItemValidator             $validator
     * @param ItemSerializer            $serializer
     * @param ItemDefinitionsCollection $itemDefinitions
     * @param HintSerializer            $hintSerializer
     */
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
        $this->repository = $this->om->getRepository('UJMExoBundle:Item\Item');
        $this->answerRepository = $this->om->getRepository('UJMExoBundle:Attempt\Answer');
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->itemDefinitions = $itemDefinitions;
        $this->hintSerializer = $hintSerializer;
    }

    public function canEdit(Item $question, User $user)
    {
        $shared = $this->om->getRepository('UJMExoBundle:Item\Shared')
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
     * Searches questions for a User.
     *
     * @param User      $user
     * @param \stdClass $filters
     * @param array     $orderBy
     * @param int       $number  - the number of questions to return
     * @param int       $page    - the offset at which we will start searching
     *
     * @return \stdClass
     */
    public function search(User $user, \stdClass $filters = null, array $orderBy = ['title' => 1], $number = -1, $page = 0)
    {
        $results = $this->repository->search($user, $filters, $orderBy, $number, $page);

        // Build search result object
        $searchResults = new \stdClass();
        $searchResults->totalResults = count($results);
        $searchResults->questions = array_map(function (Item $question) {
            return $this->export($question, [Transfer::INCLUDE_ADMIN_META, Transfer::INCLUDE_SOLUTIONS]);
        }, $results);

        // Add pagination
        $searchResults->pagination = new \stdClass();
        $searchResults->pagination->current = $page;
        $searchResults->pagination->pageSize = $number;

        // Add sorting
        $searchResults->sortBy = new \stdClass();

        return $searchResults;
    }

    /**
     * Validates and creates a new Item from raw data.
     *
     * @param \stdClass $data
     *
     * @return Item
     *
     * @throws ValidationException
     */
    public function create(\stdClass $data)
    {
        return $this->update(new Item(), $data);
    }

    /**
     * Validates and updates a Item entity with raw data.
     *
     * @param Item      $question
     * @param \stdClass $data
     *
     * @return Item
     *
     * @throws ValidationException
     */
    public function update(Item $question, \stdClass $data)
    {
        // Validate received data
        $errors = $this->validator->validate($data, [Validation::REQUIRE_SOLUTIONS]);
        if (count($errors) > 0) {
            throw new ValidationException('Question is not valid', $errors);
        }

        // Update Item with new data
        $this->serializer->deserialize($data, $question);

        // Save to DB
        $this->om->persist($question);
        $this->om->flush();

        return $question;
    }

    /**
     * Exports a question.
     *
     * @param Item  $question
     * @param array $options
     *
     * @return \stdClass
     */
    public function export(Item $question, array $options = [])
    {
        return $this->serializer->serialize($question, $options);
    }

    /**
     * Deletes a Item.
     * It's only possible if the Item is not used in an Exercise.
     *
     * @param array $questions - the uuids of questions to delete
     * @param User  $user
     */
    public function delete(array $questions, User $user)
    {
        // Reload the list of questions to delete
        $toDelete = $this->repository->findByUuids($questions);
        foreach ($toDelete as $question) {
            if ($this->canEdit($question, $user)) {
                // User has admin rights so he can delete question
                $this->om->remove($question);
            }
        }

        $this->om->flush();
    }

    /**
     * Calculates the score of an answer to a question.
     *
     * @param Item   $question
     * @param Answer $answer
     *
     * @return float
     */
    public function calculateScore(Item $question, Answer $answer)
    {
        // Let the question correct the answer
        $definition = $this->itemDefinitions->get($question->getMimeType());
        $corrected = $definition->correctAnswer($question->getInteraction(), json_decode($answer->getData()));
        if (!$corrected instanceof CorrectedAnswer) {
            $corrected = new CorrectedAnswer();
        }

        // Add hints
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

        return $this->scoreManager->calculate(json_decode($question->getScoreRule()), $corrected);
    }

    /**
     * Calculates the total score of a question.
     *
     * @param \stdClass $questionData
     *
     * @return float
     */
    public function calculateTotal(\stdClass $questionData)
    {
        // Get entities for score calculation
        $question = $this->serializer->deserialize($questionData, new Item());

        // Get the expected answer for the question
        $definition = $this->itemDefinitions->get($question->getMimeType());
        $expected = $definition->expectAnswer($question->getInteraction());

        return $this->scoreManager->calculateTotal(json_decode($question->getScoreRule()), $expected);
    }

    /**
     * Get question statistics inside an Exercise.
     *
     * @param Item     $question
     * @param Exercise $exercise
     *
     * @return \stdClass
     */
    public function getStatistics(Item $question, Exercise $exercise = null)
    {
        $questionStats = new \stdClass();

        // We load all the answers for the question (we need to get the entities as the response in DB are not processable as is)
        $answers = $this->answerRepository->findByQuestion($question, $exercise);

        // Number of Users that have seen the question
        $questionStats->seen = count($answers);

        // Grab answer data to pass it decoded to the question type
        // it doesn't need to know the whole Answer object
        $answersData = [];

        // Number of Users that have responded to the question (no blank answer)
        $questionStats->answered = 0;
        if (!empty($answers)) {
            for ($i = 0; $i < $questionStats->seen; ++$i) {
                $answer = $answers[$i];
                if (!empty($answer->getData())) {
                    ++$questionStats->answered;

                    $answersData[] = json_decode($answer->getData());
                }
            }

            // Let the handler of the question type parse and compile the data
            $definition = $this->itemDefinitions->get($question->getMimeType());
            $questionStats->solutions = $definition->getStatistics($question->getInteraction(), $answersData);
        }

        return $questionStats;
    }
}
