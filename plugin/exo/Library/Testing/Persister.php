<?php

namespace UJM\ExoBundle\Library\Testing;

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Category;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Item\ItemType;

/**
 * Simple testing utility allowing to create and persist
 * various exercise-related entities with minimal effort.
 */
class Persister
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ResourceType
     */
    private $exoType;

    /**
     * @var Role
     */
    private $userRole;

    /**
     * Persister constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param string $text
     * @param float  $order
     * @param float  $score
     *
     * @return Choice
     */
    public function qcmChoice($text, $order, $score)
    {
        $choice = new Choice();
        $choice->setUuid(uniqid('', true));
        $choice->setData($text);
        $choice->setOrder($order);
        $choice->setScore($score);
        $this->om->persist($choice);

        return $choice;
    }

    /**
     * @param string   $title
     * @param Choice[] $choices
     * @param string   $description
     *
     * @return Item
     */
    public function choiceQuestion($title, array $choices = [], $description = '')
    {
        $question = new Item();
        $question->setUuid(uniqid('', true));
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

    /**
     * @param string $title
     *
     * @return Item
     */
    public function openQuestion($title)
    {
        $question = new Item();
        $question->setScoreRule(json_encode(['type' => 'manual', 'max' => 10]));
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

    public function matchLabel($text, $score = 0)
    {
        $label = new Label();
        $label->setFeedback('feedback...');
        $label->setData($text);
        $label->setScore($score);
        $label->setUuid(uniqid('', true));
        $this->om->persist($label);

        return $label;
    }

    public function matchProposal($text, Label $label = null)
    {
        $proposal = new Proposal();
        $proposal->setData($text);
        $proposal->setUuid(uniqid('', true));
        if ($label !== null) {
            $proposal->addExpectedLabel($label);
        }
        $this->om->persist($proposal);

        return $proposal;
    }

    /**
     * Creates a match question.
     *
     * @param string $title
     * @param array  $labels
     * @param array  $proposals
     *
     * @return Item
     */
    public function matchQuestion($title, $labels = [], $proposals = [])
    {
        $question = new Item();
        $question->setUuid(uniqid('', true));
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
     * @param string $title
     * @param array  $questionData - grouping questions in sub arrays will create 1 step for one sub array
     * @param User   $user
     *
     * @return Exercise
     */
    public function exercise($title, array $questionData = [], User $user = null)
    {
        $exercise = new Exercise();
        $exercise->setUuid(uniqid('', true));

        $exercise->setDescription('This is the description of my exercise');
        if ($user) {
            if (!isset($this->exoType)) {
                $this->exoType = new ResourceType();
                $this->exoType->setName('exercise');
                $this->om->persist($this->exoType);
            }

            $node = new ResourceNode();
            $node->setName($title);
            $node->setCreator($user);
            $node->setResourceType($this->exoType);
            $node->setWorkspace($user->getPersonalWorkspace());
            $node->setClass('UJM\ExoBundle\Entity\Exercise');
            $node->setGuid(time());
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

    /**
     * @param string $username
     *
     * @return User
     */
    public function user($username)
    {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPublicUrl($username);
        $user->setPlainPassword($username);
        $user->setEmail($username.'@email.com');
        $user->setIsMailValidated(true);
        $this->om->persist($user);

        if (!$this->userRole) {
            $this->userRole = $this->role('ROLE_USER');
            $this->om->persist($this->userRole);
        }

        $user->addRole($this->userRole);

        $workspace = new Workspace();
        $workspace->setName($username);
        $workspace->setCreator($user);
        $workspace->setCode($username);
        $workspace->setGuid($username);
        $this->om->persist($workspace);

        $user->setPersonalWorkspace($workspace);

        return $user;
    }

    /**
     * Creates a category.
     *
     * @param string $name
     * @param User   $user
     *
     * @return Category
     */
    public function category($name, User $user = null)
    {
        $category = new Category();
        $category->setUuid(uniqid('', true));
        $category->setName($name);

        if (!empty($user)) {
            $category->setUser($user);
        }

        $this->om->persist($category);

        return $category;
    }

    /**
     * @param string $name
     *
     * @return Role
     */
    public function role($name)
    {
        $role = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneBy([
            'name' => $name,
        ]);

        if (!$role) {
            $role = new Role();
            $role->setName($name);
            $role->setTranslationKey($name);
            $this->om->persist($role);
        }

        return $role;
    }

    public function maskDecoder(ResourceType $type, $permission, $value)
    {
        $decoder = new MaskDecoder();
        $decoder->setResourceType($type);
        $decoder->setName($permission);
        $decoder->setValue($value);
        $this->om->persist($decoder);

        return $decoder;
    }

    public function hint(Item $question, $text, $penalty = 1)
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
