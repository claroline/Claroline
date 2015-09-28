<?php

namespace UJM\ExoBundle\Testing;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\Question;

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

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param string    $text
     * @param float     $score
     * @return Choice
     */
    public function qcmChoice($text, $score)
    {
        $choice = new Choice();
        $choice->setLabel($text);
        $choice->setWeight($score);
        $this->om->persist($choice);

        return $choice;
    }

    /**
     * @param string    $title
     * @param Choice[]  $choices
     * @return Question
     */
    public function qcmQuestion($title, array $choices = [])
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setInvite('Invite...');

        $interactionQcm = new InteractionQCM();
        $interactionQcm->setQuestion($question);

        for ($i = 0, $max = count($choices); $i < $max; ++$i) {
            $choices[$i]->setInteractionQCM($interactionQcm);
            $choices[$i]->setOrdre($i);
        }

        $this->om->persist($interactionQcm);
        $this->om->persist($question);

        return $question;
    }

    /**
     * @param string $title
     * @return Question
     */
    public function openQuestion($title)
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setInvite('Invite...');

        $interactionQcm = new InteractionOpen();
        $interactionQcm->setQuestion($question);

        $this->om->persist($interactionQcm);
        $this->om->persist($question);

        return $question;
    }

    /**
     * @param string        $title
     * @param Question[]    $questions
     * @param User          $user
     * @return Exercise
     */
    public function exercise($title, array $questions = [], User $user = null)
    {
        $exercise = new Exercise();
        $exercise->setTitle($title);

        for ($i = 0, $max = count($questions); $i < $max; ++$i) {
            $link = new ExerciseQuestion($exercise, $questions[$i]);
            $link->setOrdre($i);
            $this->om->persist($link);
        }

        if ($user) {
            if (!$this->exoType) {
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
            $exercise->setResourceNode($node);
            $this->om->persist($node);
        }

        $this->om->persist($exercise);

        return $exercise;
    }

    /**
     * @param string $username
     * @return User
     */
    public function user($username)
    {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setMail($username . '@mail.com');
        $this->om->persist($user);

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
     * @param string $name
     * @return Category
     */
    public function category($name)
    {
        $category = new Category();
        $category->setValue($name);
        $this->om->persist($category);

        return $category;
    }
}
