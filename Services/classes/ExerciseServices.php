<?php

namespace UJM\ExoBundle\Services\classes;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
// use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepQuestion;

class ExerciseServices
{
    protected $om;
    protected $authorizationChecker;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;
    protected $doctrine;
    protected $container;

    /**
     * Constructor.
     *
     *
     * @param \Claroline\CoreBundle\Persistence\ObjectManager                              $om                   Dependency Injection
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Dependency Injection
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface                  $eventDispatcher      Dependency Injection
     * @param \Doctrine\Bundle\DoctrineBundle\Registry                                     $doctrine             Dependency Injection;
     * @param \Symfony\Component\DependencyInjection\Container                             $container
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        Registry $doctrine,
        Container $container
    ) {
        $this->om = $om;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->container = $container;
    }

    /**
     * Return the number of papers for an exercise and for an user.
     *
     *
     * @param int  $uid      id User
     * @param int  $exoId    id Exercise
     * @param bool $finished to count or no paper n o finished
     *
     * @return int
     */
    public function getNbPaper($uid, $exoID, $finished = false)
    {
        $papers = $this->om
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($uid, $exoID, $finished);

        return count($papers);
    }

    /**
     * Get max score possible for an exercise.
     *
     *
     * @param UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return float
     */
    public function getExerciseTotalScore($exercise)
    {
        $exoTotalScore = 0;

        $questions = $this->om
                    ->getRepository('UJMExoBundle:Question')
                    ->findByExercise($exercise);

        foreach ($questions as $question) {
            $typeInter = $question->getType();
            $interSer = $this->container->get('ujm.exo_'.$typeInter);
            $interactionX = $interSer->getInteractionX($question->getId());
            $scoreMax = $interSer->maxScore($interactionX);
            $exoTotalScore += $scoreMax;
        }

        return $exoTotalScore;
    }

    // /**
    //  * To link a question with an exercise.
    //  *
    //  *
    //  * @param UJM\ExoBundle\Entity\Exercise               $exercise instance of Exercise
    //  * @param InteractionQCM or InteractionGraphic or ... $interX
    //  */
    // public function setExerciseQuestion($exercise, $interX, $order = -1)
    // {
    //     $eq = new ExerciseQuestion($exercise, $interX->getQuestion());
    //
    //     if ($order == -1) {
    //         $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
    //               .'WHERE eq.exercise='.$exercise->getId();
    //         $query = $this->doctrine->getManager()->createQuery($dql);
    //         $maxOrdre = $query->getResult();
    //
    //         $eq->setOrdre((int) $maxOrdre[0][1] + 1);
    //     } else {
    //         $eq->setOrdre($order);
    //     }
    //     $this->om->persist($eq);
    //
    //     $this->om->flush();
    // }

    /**
     * To know if an user is the creator of an exercise.
     *
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return bool
     */
    public function isExerciseAdmin($exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode()));
        if ($this->authorizationChecker->isGranted('ADMINISTRATE', $collection)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * For all papers for an user and an exercise get scorePaper, maxExoScore, scoreTemp (all questions marked or no).
     *
     *
     * @param int $userId id User
     * @param int $exoId  id Exercise
     *
     * @return array
     */
    public function getScoresUser($userId, $exoId)
    {
        $tabScoresUser = array();
        $i = 0;

        $papers = $this->om
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($userId, $exoId);

        foreach ($papers as $paper) {
            $infosPaper = $this->container->get('ujm.exo_paper')->getInfosPaper($paper);
            $tabScoresUser[$i]['score'] = $infosPaper['scorePaper'];
            $tabScoresUser[$i]['maxExoScore'] = $infosPaper['maxExoScore'];
            $tabScoresUser[$i]['scoreTemp'] = $infosPaper['scoreTemp'];

            ++$i;
        }

        return $tabScoresUser;
    }

    /**
     * Trigger an event to log informations after to execute an exercise if the score is not temporary.
     *
     *
     * @param \UJM\ExoBundle\Entity\Paper\paper $paper
     */
    public function manageEndOfExercise(Paper $paper)
    {
        $paperInfos = $this->container->get('ujm.exo_paper')->getInfosPaper($paper);

        if (!$paperInfos['scoreTemp']) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), $paperInfos);
            $this->eventDispatcher->dispatch('log', $event);
        }
    }

    /**
     * To control the max attemps, allow to know if an user can again execute an exercise.
     *
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     * @param int                            $uid
     * @param bool                           $exoAdmin
     *
     * @return bool
     */
    public function controlMaxAttemps($exercise, $uid, $exoAdmin)
    {
        if (($exoAdmin === false) && ($exercise->getMaxAttempts() > 0)
            && ($exercise->getMaxAttempts() <= $this->getNbPaper($uid,
            $exercise->getId(), true))
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Add an Interaction in an exercise if created from an exercise.
     *
     *
     * @param UJM\ExoBundle\Entity\Question $question
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise
     * @param UJM\ExoBundle\Entity\Step     $step
     * @param Doctrine EntityManager        $em
     */
    public function addQuestionInExercise($question, $exercise, $step)
    {
        if (null != $exercise) {
            if ($this->isExerciseAdmin($exercise)) {
                if (null == $step) {
                    // Create a new Step to add the Question
                    $this->createStepForOneQuestion($exercise,$question, 1);
                } else {
                    // Add the question to the existing Step
                    $em = $this->doctrine->getManager();

                    $sq = new StepQuestion();
                    $sq->setStep($step);
                    $sq->setQuestion($question);
                    $sq->setOrdre($step->getNbQuestion() + 1);
                    $em->persist($sq);
                    $em->flush();
                }
            }
        }
    }

    /**
     * Add a question in a step
     *
     *
     * @param UJM\ExoBundle\Entity\Question $question
     * @param UJM\ExoBundle\Entity\Step $step
     * @param Integer $order
     */
    public function addQuestionInStep($question, $step, $order)
    {
        if ($step != null) {
            if ($this->isExerciseAdmin($step->getExercise())) {
                $sq = new StepQuestion($step, $question);

                if ($order == -1) {
                    $dql = 'SELECT max(sq.ordre) FROM UJM\ExoBundle\Entity\StepQuestion sq '
                          .'WHERE sq.step='.$step->getId();
                    $query = $this->doctrine->getManager()->createQuery($dql);
                    $maxOrdre = $query->getResult();

                    $sq->setOrdre((int) $maxOrdre[0][1] + 1);
                } else {
                    $sq->setOrdre($order);
                }

                $this->om->persist($sq);
                $this->om->flush();
            }
        }
    }

    /**
     * To know if an user is allowed to open an exercise.
     *
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return bool
     */
    public function allowToOpen($exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode()));
        if ($this->authorizationChecker->isGranted('OPEN', $collection)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        $user = $this->container->get('security.token_storage')
                                ->getToken()->getUser();

        return $user;
    }

    /**
     * @return int or String
     */
    public function getUserId()
    {
        $user = $this->getUser();
        if (is_object($user)) {
            $uid = $user->getId();
        } else {
            $uid = 'anonymous';
        }

        return $uid;
    }

    /**
     * Temporary : Waiting step manager
     *
     * Create a step for one question in the exercise
     *
     * @param Exercise $exercise
     * @param Question $question
     * @param int $orderStep order of the step in the exercise
     */
    public function createStepForOneQuestion(Exercise $exercise,
            Question $question, $orderStep) {
                $em = $this->doctrine->getManager();
                $step = $this->createStep($exercise, $orderStep, $em);

                $sq = new StepQuestion();
                $sq->setStep($step);
                $sq->setQuestion($question);
                $sq->setOrdre('1');
                $em->persist($sq);
                $em->flush();
    }

    /**
     *
     * @param Exercise $exercise
     * @param type $orderStep
     * @param type $em
     * @return Step
     */
    public function createStep (Exercise $exercise, $orderStep, $em) {

        //Creating a step by question
        $step = new Step();
        $step->setText(' ');
        $step->setExercise($exercise);
        $step->setNbQuestion('0');
        $step->setDuration(0);
        $step->setMaxAttempts(0);
        $step->setOrder($orderStep);
        $em->persist($step);

        return $step;
    }
}
