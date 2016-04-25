<?php

namespace UJM\ExoBundle\Testing;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepQuestion;
use UJM\ExoBundle\Entity\Choice;
use UJM\ExoBundle\Entity\Proposal;
use UJM\ExoBundle\Entity\Label;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\InteractionOpen;
use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\InteractionMatching;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\TypeQCM;
use UJM\ExoBundle\Entity\TypeMatching;
use UJM\ExoBundle\Manager\PaperManager;

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
     * @var TypeQCM
     */
    private $multipleChoiceType;

    /**
     * @var TypeMatch
     */
    private $matchType;

    /**
     * @var PaperManager
     */
    private $paperManager;

    public function __construct(ObjectManager $om, PaperManager $paperManager)
    {
        $this->om = $om;
        $this->paperManager = $paperManager;
    }

    /**
     * @param string $text
     * @param float  $score
     *
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
     * @param string   $title
     * @param Choice[] $choices
     * @param string   $description
     *
     * @return Question
     */
    public function qcmQuestion($title, array $choices = [], $description = '')
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setInvite('Invite...');
        $question->setDescription($description);

        if (!$this->multipleChoiceType) {
            $this->multipleChoiceType = new TypeQCM();
            $this->multipleChoiceType->setCode(1);
            $this->multipleChoiceType->setValue('Multiple response');
            $this->om->persist($this->multipleChoiceType);
        }

        $interactionQcm = new InteractionQCM();
        $interactionQcm->setQuestion($question);
        $interactionQcm->setTypeQCM($this->multipleChoiceType);
        $interactionQcm->setWeightResponse(true);

        for ($i = 0, $max = count($choices); $i < $max; ++$i) {
            $choices[$i]->setOrdre($i);
            $interactionQcm->addChoice($choices[$i]);
        }

        $this->om->persist($interactionQcm);
        $this->om->persist($question);

        return $question;
    }

    /**
     * @param string $title
     *
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

    public function matchLabel($text, $score = 0)
    {
        $label = new Label();
        $label->setFeedback('feedback...');
        $label->setValue($text);
        $label->setScoreRightResponse($score);
        $this->om->persist($label);

        return $label;
    }

    public function matchProposal($text, Label $label = null)
    {
        $proposal = new Proposal();
        $proposal->setValue($text);
        if ($label !== null) {
            $proposal->addAssociatedLabel($label);
        }
        $this->om->persist($proposal);

        return $proposal;
    }

    public function matchQuestion($title, $labels = [], $proposals = [])
    {
        $question = new Question();
        $question->setTitle($title);
        $question->setInvite('Invite...');

        if (!$this->matchType) {
            $this->matchType = new TypeMatching();
            $this->matchType->setCode(1);
            $this->matchType->setValue('To Bind');
            $this->om->persist($this->matchType);
        }

        $interactionMatching = new InteractionMatching();
        $interactionMatching->setQuestion($question);
        $interactionMatching->setShuffle(false);
        $interactionMatching->setTypeMatching($this->matchType);

        for ($i = 0, $max = count($labels); $i < $max; ++$i) {
            $labels[$i]->setOrdre($i);
            $interactionMatching->addLabel($labels[$i]);
        }

        for ($i = 0, $max = count($proposals); $i < $max; ++$i) {
            $proposals[$i]->setOrdre($i + 1);
            $interactionMatching->addProposal($proposals[$i]);
        }

        $this->om->persist($interactionMatching);
        $this->om->persist($question);

        return $question;
    }

     /**
      * @param string        $title
      * @param Question[]    $questions
      * @param User          $user
      *
      * @return Exercise
      */
     public function exercise($title, array $questions = [], User $user = null)
     {
         $exercise = new Exercise();
         $exercise->setTitle($title);

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
             $node->setGuid(time());
             $exercise->setResourceNode($node);
             $this->om->persist($node);
         }

         $this->om->persist($exercise);

         for ($i = 0, $max = count($questions); $i < $max; ++$i) {
             $step = new Step();
             $step->setText('step');
             $step->setOrder($i);
             $step->setExercise($exercise);
             $this->om->persist($step);
             $stepQuestion = new StepQuestion();
             $stepQuestion->setStep($step);
             $stepQuestion->setQuestion($questions[$i]);
             $stepQuestion->setOrdre(0);
             $this->om->persist($stepQuestion);

             /*$link = new ExerciseQuestion($exercise, $questions[$i]);
             $link->setOrdre($i);
             $this->om->persist($link);*/
         }

         return $exercise;
     }

    /**
     * @param User     $user
     * @param Exercise $exercise
     *
     * @return Paper
     */
    public function paper(User $user, Exercise $exercise)
    {
        return $this->paperManager->createPaper($user, $exercise);
    }

    public function finishpaper(Paper $paper)
    {
        return $this->paperManager->finishPaper($paper);
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
        $user->setPassword($username);
        $user->setMail($username.'@mail.com');
        $user->setGuid($username);
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
     * @param string $name
     *
     * @return Category
     */
    public function category($name)
    {
        $category = new Category();
        $category->setValue($name);
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
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($name);
        $this->om->persist($role);

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

    public function hint(Question $question, $text, $penalty = 1)
    {
        $hint = new Hint();
        $hint->setValue($text);
        $hint->setPenalty($penalty);
        $hint->setQuestion($question);
        $this->om->persist($hint);

        return $hint;
    }
}
