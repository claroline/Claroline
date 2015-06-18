<?php

namespace UJM\ExoBundle\Services\classes;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;

use Doctrine\Bundle\DoctrineBundle\Registry;

use \Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;

class ExerciseServices
{
    protected $om;
    protected $tokenStorage;
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
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Dependency Injection
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher Dependency Injection
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        Registry $doctrine,
        Container $container
    )
    {
        $this->om = $om;
        $this->tokenStorage         = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher      = $eventDispatcher;
        $this->doctrine             = $doctrine;
        $this->container            = $container;
    }

    /**
     * Get IP client
     *
     * @access public
     * @param Request $request
     *
     * @return IP Client
     */
    public function getIP(Request $request)
    {// paper service

        return $request->getClientIp();
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
    {// service exercice
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
    {// service exercice
        $exoTotalScore = 0;

        $eqs = $this->om
                    ->getRepository('UJMExoBundle:ExerciseQuestion')
                    ->findBy(array('exercise' => $exoID));

        foreach ($eqs as $eq) {
            $interaction = $this->om
                                ->getRepository('UJMExoBundle:Interaction')
                                ->getInteraction($eq->getQuestion()->getId());

            $interSer        = $this->container->get('ujm.' . $typeInter);
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
    {// service exercice
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
    {// service exercice
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
    {// service exercice
        $tabScoresUser = array();
        $i = 0;

        $papers = $this->om
                       ->getRepository('UJMExoBundle:Paper')
                       ->getExerciseUserPapers($userId, $exoId);

        foreach ($papers as $paper) {
            $infosPaper = $this->getInfosPaper($paper);
            $tabScoresUser[$i]['score']       = $infosPaper['scorePaper'];
            $tabScoresUser[$i]['maxExoScore'] = $infosPaper['maxExoScore'];
            $tabScoresUser[$i]['scoreTemp']   = $infosPaper['scoreTemp'];

            $i++;
        }

        return $tabScoresUser;
    }

    /**
     * To control the User's rights to this shared question
     *
     * @access public
     *
     * @param integer $questionID id Question
     *
     * @return array
     */
    public function controlUserSharedQuestion($questionID)
    {// service question
        $user = $this->tokenStorage->getToken()->getUser();

        $questions = $this->om
                          ->getRepository('UJMExoBundle:Share')
                          ->getControlSharedQuestion($user->getId(), $questionID);

        return $questions;
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
    {// service exercice
        $paperInfos = $this->getInfosPaper($paper);

        if (!$paperInfos['scoreTemp']) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), $paperInfos);
            $this->eventDispatcher->dispatch('log', $event);
        }
    }

    /**
     * Get information if these categories are linked to questions, allow to know if a category can be deleted or not
     *
     * @access public
     *
     * @return boolean[]
     */
    public function getLinkedCategories()
    {// service question
        $linkedCategory = array();
        $repositoryCategory = $this->om
                                   ->getRepository('UJMExoBundle:Category');

        $repositoryQuestion = $this->om
                                   ->getRepository('UJMExoBundle:Question');

        $categoryList = $repositoryCategory->findAll();


        foreach ($categoryList as $category) {
          $questionLink = $repositoryQuestion->findOneBy(array('category' => $category->getId()));
          if (!$questionLink) {
              $linkedCategory[$category->getId()] = 0;
          } else {
              $linkedCategory[$category->getId()] = 1;
          }
        }

        return $linkedCategory;
    }

    /**
     * To control the max attemps, allow to know if an user can again execute an exercise
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     * @param \UJM\ExoBundle\Entity\User $user
     * @param boolean $exoAdmin
     *
     * @return boolean
     */
    public function controlMaxAttemps($exercise, $user, $exoAdmin)
    {// service exercice
        if (($exoAdmin === false) && ($exercise->getMaxAttempts() > 0)
            && ($exercise->getMaxAttempts() <= $this->getNbPaper($user->getId(),
            $exercise->getId(), true))
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * The user must be registered (and the dates must be good or the user must to be admin for the exercise)
     *
     * @access public
     *
     * @param boolean $exoAdmin
     * @param \UJM\ExoBundle\Entity\Exercise $exercise
     *
     * @return boolean
     */
    public function controlDate($exoAdmin, $exercise)
    {// service exercice
        if (
            ((($exercise->getStartDate()->format('Y-m-d H:i:s') <= date('Y-m-d H:i:s'))
            && (($exercise->getUseDateEnd() == 0)
            || ($exercise->getEndDate()->format('Y-m-d H:i:s') >= date('Y-m-d H:i:s'))))
            || ($exoAdmin === true))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * Call after applied a filter in a questions list to know the actions allowed for each interaction
     *
     * @access public
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $listInteractions
     * @param integer $userID id User
     * @param Doctrine EntityManager $em
     *
     * @return array
     */
    public function getActionsAllQuestions($listInteractions, $userID, $em)
    {// service question
        $interServ= $this->container->get('ujm.Interaction_general');

        $allActions           = array();
        $actionQ              = array();
        $questionWithResponse = array();
        $alreadyShared        = array();
        $sharedWithMe         = array();
        $shareRight           = array();

        foreach ($listInteractions as $interaction) {
                if ($interaction->getQuestion()->getUser()->getId() == $userID) {
                    $actionQ[$interaction->getQuestion()->getId()] = 1; // my question

                    $actions = $interServ->getActionInteraction($interaction);
                    $questionWithResponse += $actions[0];
                    $alreadyShared += $actions[1];
                } else {
                    $sharedQ = $em->getRepository('UJMExoBundle:Share')
                    ->findOneBy(array('user' => $userID, 'question' => $interaction->getQuestion()->getId()));

                    if (count($sharedQ) > 0) {
                        $actionQ[$interaction->getQuestion()->getId()] = 2; // shared question

                        $actionsS = $this->getActionShared($em, $sharedQ);
                        $sharedWithMe += $actionsS[0];
                        $shareRight += $actionsS[1];
                        $questionWithResponse += $actionsS[2];
                    } else {
                        $actionQ[$interaction->getQuestion()->getId()] = 3; // other
                    }
                }
            }

        $allActions[0] = $actionQ;
        $allActions[1] = $questionWithResponse;
        $allActions[2] = $alreadyShared;
        $allActions[3] = $sharedWithMe;
        $allActions[4] = $shareRight;

        return $allActions;
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
    {//service exercice
        if ($exercise != null) {
            if ($this->isExerciseAdmin($exercise)) {
                $this->setExerciseQuestion($exercise, $inter);
            }
        }
    }

    /**
     * To control the User's rights to this question
     *
     * @access public
     *
     * @param integer $questionID id Question
     *
     * @return Doctrine Query Result
     */
    public function controlUserQuestion($questionID, $container, $em)
    {//service question
        $user = $container->get('security.token_storage')->getToken()->getUser();

        $question = $em
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $questionID);

        return $question;
    }
}
