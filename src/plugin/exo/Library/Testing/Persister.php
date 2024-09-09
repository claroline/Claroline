<?php

namespace UJM\ExoBundle\Library\Testing;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\Persister as BasePersister;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Item\ItemType;

/**
 * Simple testing utility allowing to create and persist
 * various exercise-related entities with minimal effort.
 */
class Persister
{
    private ?ResourceType $exoType = null;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly BasePersister $persister
    ) {
    }

    public function qcmChoice(string $text, int $order, float $score): Choice
    {
        $choice = new Choice();
        $choice->setUuid(uniqid('', true));
        $choice->setData($text);
        $choice->setOrder($order);
        $choice->setScore($score);
        $this->om->persist($choice);

        return $choice;
    }

    public function choiceQuestion(string $title, ?array $choices = [], ?string $description = ''): Item
    {
        $question = new Item();
        $question->setUuid(uniqid('', true));
        $question->setExpectedAnswers(true);
        $question->setMimeType(ItemType::CHOICE);
        $question->setTitle($title);
        $question->setContent('Invite...');
        $question->setDescription($description);
        $question->setScoreRule('{"type": "sum"}');

        $interactionQcm = new ChoiceQuestion();
        $interactionQcm->setQuestion($question);
        $interactionQcm->setMultiple(true);

        for ($i = 0, $max = count($choices); $i < $max; ++$i) {
            $choices[$i]->setOrder($i);
            $interactionQcm->addChoice($choices[$i]);
        }

        $this->om->persist($interactionQcm);
        $this->om->persist($question);

        return $question;
    }

    public function openQuestion(string $title): Item
    {
        $question = new Item();
        $question->setScoreRule(json_encode(['type' => 'manual', 'max' => 10]));
        $question->setExpectedAnswers(true);
        $question->setUuid(uniqid('', true));
        $question->setMimeType(ItemType::OPEN);
        $question->setTitle($title);
        $question->setContent('Invite...');
        $question->setScoreRule('{"type": "manual", "max": 10}');

        $interactionOpen = new OpenQuestion();
        $interactionOpen->setQuestion($question);
        $interactionOpen->setAnswerMaxLength(1000);

        $this->om->persist($interactionOpen);
        $this->om->persist($question);

        return $question;
    }

    /**
     * Creates a match question.
     */
    public function matchQuestion(string $title, ?array $labels = [], ?array $proposals = []): Item
    {
        $question = new Item();
        $question->setExpectedAnswers(true);
        $question->setMimeType(ItemType::MATCH);
        $question->setScoreRule('{"type": "sum"}');
        $question->setTitle($title);
        $question->setContent('Invite...');

        $interactionMatching = new MatchQuestion();
        $interactionMatching->setQuestion($question);
        $interactionMatching->setShuffle(false);

        for ($i = 0, $max = count($labels); $i < $max; ++$i) {
            $labels[$i]->setOrder($i);
            $interactionMatching->addLabel($labels[$i]);
        }

        for ($i = 0, $max = count($proposals); $i < $max; ++$i) {
            $proposals[$i]->setOrder($i + 1);
            $interactionMatching->addProposal($proposals[$i]);
        }

        $this->om->persist($interactionMatching);
        $this->om->persist($question);

        return $question;
    }

    /**
     * @param array $questionData - grouping questions in sub arrays will create 1 step for one sub array
     */
    public function exercise(string $title, array $questionData = [], User $user = null): Exercise
    {
        $exercise = new Exercise();
        $exercise->setExpectedAnswers(true);
        $exercise->setScoreRule(json_encode(['type' => 'sum']));

        $exercise->setOverviewMessage('This is the description of my exercise');
        if ($user) {
            if (!isset($this->exoType)) {
                $this->exoType = new ResourceType();
                $this->exoType->setName('exercise');
                $this->exoType->setClass('UJM\ExoBundle\Entity\Exercise');
                $this->om->persist($this->exoType);
            }

            $node = new ResourceNode();
            $node->setName($title);
            $node->setCode($title);
            $node->setCreator($user);
            $node->setResourceType($this->exoType);
            $node->setWorkspace($user->getPersonalWorkspace());
            $node->setUuid(uniqid('', true));
            $exercise->setResourceNode($node);
            $this->om->persist($node);
        }

        $this->om->persist($exercise);

        foreach ($questionData as $index => $data) {
            $step = new Step();
            $step->setUuid(uniqid($index));
            $step->setDescription('step');
            $step->setOrder($index);

            // Add step to the exercise
            $exercise->addStep($step);
            if (is_array($data)) {
                foreach ($data as $question) {
                    $step->addQuestion($question);
                }
            } else {
                $step->addQuestion($data);
            }
        }

        return $exercise;
    }

    public function user(string $username): User
    {
        return $this->persister->user($username, true);
    }

    public function role(string $name): Role
    {
        return $this->persister->role($name);
    }

    public function maskDecoder(ResourceType $type, string $permission, int $value): MaskDecoder
    {
        return $this->persister->maskDecoder($type, $permission, $value);
    }

    public function hint(Item $question, string $text, float $penalty = 1): Hint
    {
        $hint = new Hint();
        $hint->setData($text);
        $hint->setUuid(uniqid('', true));
        $hint->setPenalty($penalty);

        $question->addHint($hint);

        $this->om->persist($hint);

        return $hint;
    }
}
