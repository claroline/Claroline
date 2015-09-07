<?php

namespace UJM\ExoBundle\Services\classes;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;

use Doctrine\Bundle\DoctrineBundle\Registry;

use \Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;

class ExerciseServices
{
    protected $om;
    protected $authorizationChecker;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;
    protected $doctrine;
    protected $container;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Claroline\CoreBundle\Persistence\ObjectManager $om Dependency Injection
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Dependency Injection
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Dependency Injection
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        Registry $doctrine,
        Container $container
    )
    {
        $this->om = $om;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher      = $eventDispatcher;
        $this->doctrine             = $doctrine;
        $this->container            = $container;
    }

    /**
     * Return the number of papers for an exercise and for an user
     *
     * @access public
     *
     * @param integer $uid id User
     * @param integer $exoId id Exercise
     * @param boolean $finished to count or no paper n o finished
     *
     * @return integer
     */
    public function getNbPaper($uid, $exoID, $finished = false)
    {
        $papers = $this->om
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($uid, $exoID, $finished);

        return count($papers);
    }

    /**
     * Get max score possible for an exercise
     *
     * @access public
     *
     * @param integer $exoID id Exercise
     *
     * @return float
     */
    public function getExerciseTotalScore($exoID)
    {
        $exoTotalScore = 0;

        $eqs = $this->om
                    ->getRepository('UJMExoBundle:ExerciseQuestion')
                    ->findBy(array('exercise' => $exoID));

        foreach ($eqs as $eq) {
            $interaction = $this->om
                                ->getRepository('UJMExoBundle:Interaction')
                                ->getInteraction($eq->getQuestion()->getId());
            $typeInter = $interaction->getType();

            $interSer        = $this->container->get('ujm.exo_' . $typeInter);
            $interactionX    = $interSer->getInteractionX($interaction->getId());
            $scoreMax        = $interSer->maxScore($interactionX);

            $exoTotalScore += $scoreMax;
        }

        return $exoTotalScore;
    }

    /**
     * To link a question with an exercise
     *
     * @access public
     *
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise
     * @param InteractionQCM or InteractionGraphic or ... $interX
     *
     */
    public function setExerciseQuestion($exercise, $interX, $order = -1)
    {
        $eq = new ExerciseQuestion($exercise, $interX->getInteraction()->getQuestion());

        if ($order == -1) {
            $dql = 'SELECT max(eq.ordre) FROM UJM\ExoBundle\Entity\ExerciseQuestion eq '
                  . 'WHERE eq.exercise='.$exercise->getId();
            $query = $this->doctrine->getManager()->createQuery($dql);
            $maxOrdre = $query->getResult();

            $eq->setOrdre((int) $maxOrdre[0][1] + 1);
        } else {
            $eq->setOrdre($order);
        }
        $this->om->persist($eq);

        $this->om->flush();
    }

    /**
     * To know if an user is the creator of an exercise
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return boolean
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
     * For all papers for an user and an exercise get scorePaper, maxExoScore, scoreTemp (all questions marked or no)
     *
     * @access public
     *
     * @param integer $userId id User
     * @param integer $exoId id Exercise
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
            $tabScoresUser[$i]['score']       = $infosPaper['scorePaper'];
            $tabScoresUser[$i]['maxExoScore'] = $infosPaper['maxExoScore'];
            $tabScoresUser[$i]['scoreTemp']   = $infosPaper['scoreTemp'];

            $i++;
        }

        return $tabScoresUser;
    }

    /**
     * Trigger an event to log informations after to execute an exercise if the score is not temporary
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\paper $paper
     *
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
     * To control the max attemps, allow to know if an user can again execute an exercise
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     * @param integer $uid
     * @param boolean $exoAdmin
     *
     * @return boolean
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
     * Add an Interaction in an exercise if created from an exercise
     *
     * @access public
     *
     * @param type $inter
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise
     * @param Doctrine EntityManager $em
     */
    public function addQuestionInExercise($inter, $exercise)
    {
        if ($exercise != null) {
            if ($this->isExerciseAdmin($exercise)) {
                $this->setExerciseQuestion($exercise, $inter);
            }
        }
    }

    /**
     * To know if an user is allowed to open an exercise
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return boolean
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
     *
     * @access public
     *
     * @return Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        $user = $this->container->get('security.token_storage')
                                ->getToken()->getUser();

        return $user;

    }

    /**
     *
     * @access public
     *
     * @return integer or String
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

}
