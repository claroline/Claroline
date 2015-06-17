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
    {

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

            $interSer        = $this->container->get('ujm.' . $typeInter);
            $interactionX    = $interSer->getInteractionX($interaction->getId());
            $scoreMax        = $interSer->maxScore($interactionX);

            $exoTotalScore += $scoreMax;
        }

        return $exoTotalScore;
    }

    /**
     * Get total score for an paper
     *
     * @access public
     *
     * @param integer $paperID id Paper
     *
     * @return float
     */
    public function getExercisePaperTotalScore($paperID)
    {
        $exercisePaperTotalScore = 0;
        $paper = $interaction = $this->om
                                     ->getRepository('UJMExoBundle:Paper')
                                     ->find($paperID);

        $interQuestions = $paper->getOrdreQuestion();
        $interQuestions = substr($interQuestions, 0, strlen($interQuestions) - 1);
        $interQuestionsTab = explode(";", $interQuestions);

        foreach ($interQuestionsTab as $interQuestion) {
            $interaction = $this->om->getRepository('UJMExoBundle:Interaction')->find($interQuestion);
            $interSer        = $this->container->get('ujm.' . $interaction->getType());
            $interactionX    = $interSer->getInteractionX($interaction->getId());
            $exercisePaperTotalScore += $interSer->maxScore($interactionX);
        }

        return $exercisePaperTotalScore;
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
     * To round up and down a score
     *
     * @access public
     *
     * @param float $toBeAdjusted
     *
     * @return float
     */
    public function roundUpDown($toBeAdjusted)
    {
        return (round($toBeAdjusted / 0.5) * 0.5);
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
     * Get informations about a paper response, maxExoScore, scorePaper, scoreTemp (all questions graphiced or no)
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Paper\paper $paper
     *
     * @return array
     */
    public function getInfosPaper($paper)
    {
        $infosPaper = array();
        $scorePaper = 0;
        $scoreTemp = false;

        $em = $this->doctrine->getManager();

        $interactions = $this->om
                             ->getRepository('UJMExoBundle:Interaction')
                             ->getPaperInteraction($em, str_replace(';', '\',\'', substr($paper->getOrdreQuestion(), 0, -1)));

        $interactions = $this->orderInteractions($interactions, $paper->getOrdreQuestion());

        $infosPaper['interactions'] = $interactions;

        $responses = $this->om
                          ->getRepository('UJMExoBundle:Response')
                          ->getPaperResponses($paper->getUser()->getId(), $paper->getId());

        $responses = $this->orderResponses($responses, $paper->getOrdreQuestion());

        $infosPaper['responses'] = $responses;

        $infosPaper['maxExoScore'] = $this->getExercisePaperTotalScore($paper->getId());

        foreach ($responses as $response) {
            if ($response->getMark() != -1) {
                $scorePaper += $response->getMark();
            } else {
                $scoreTemp = true;
            }
        }

        $infosPaper['scorePaper'] = $scorePaper;
        $infosPaper['scoreTemp'] = $scoreTemp;

        return $infosPaper;
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
    {
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
    {
        $paperInfos = $this->getInfosPaper($paper);

        if (!$paperInfos['scoreTemp']) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), $paperInfos);
            $this->eventDispatcher->dispatch('log', $event);
        }
    }

    /**
     * Get information if the categories are linked with question, allow to know if a category can be deleted or no
     *
     * @access public
     *
     * @return array[boolean]
     */
    public function getLinkedCategories()
    {
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
    {
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
    {
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
    {
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
     * Get the types of open question long, short, numeric, one word
     *
     * @access public
     *
     * @return array
     */
    public function getTypeOpen()
    {
        $em = $this->doctrine->getManager();

        $typeOpen = array();
        $types = $em->getRepository('UJMExoBundle:TypeOpenQuestion')
                    ->findAll();

        foreach ($types as $type) {
            $typeOpen[$type->getId()] = $type->getCode();
        }

        return $typeOpen;
    }

    /**
     * Get the types of Matching, Multiple response, unique response
     *
     * @access public
     *
     * @return array
     */
    public function getTypeMatching()
    {
        $em = $this->doctrine->getManager();

        $typeMatching = array();
        $types = $em->getRepository('UJMExoBundle:TypeMatching')
                    ->findAll();

        foreach ($types as $type) {
            $typeMatching[$type->getId()] = $type->getCode();
        }

        return $typeMatching;
    }

    /**
     * Get interactions in order for a paper
     *
     * @access private
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $interactions
     * @param String $order
     *
     * @return array[Interaction]
     */
    private function orderInteractions($interactions, $order)
    {
        $inter = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);

        foreach ($order as $interId) {
            foreach ($interactions as $key => $interaction) {
                if ($interaction->getId() == $interId) {
                    $inter[] = $interaction;
                    unset($interactions[$key]);
                    break;
                }
            }
        }

        return $inter;
    }

    /**
     * Get responses in order for a paper
     *
     * @access private
     *
     * @param Collection of \UJM\ExoBundle\Entity\Response $responses
     * @param String $order
     *
     * @çeturn array[Interaction]
     */
    private function orderResponses($responses, $order)
    {
        $resp = array();
        $order = substr($order, 0, strlen($order) - 1);
        $order = explode(';', $order);
        foreach ($order as $interId) {
            $tem = 0;
            foreach ($responses as $key => $response) {
                if ($response->getInteraction()->getId() == $interId) {
                    $tem++;
                    $resp[] = $response;
                    unset($responses[$key]);
                    break;
                }
            }
            //if no response
            if ($tem == 0) {
                $response = new \UJM\ExoBundle\Entity\Response();
                $response->setResponse('');
                $response->setMark(0);

                $resp[] = $response;
            }
        }

        return $resp;
    }

    /**
     * Add an Interaction in an exercise if created since an exercise
     *
     * @access public
     *
     * @param type $inter
     * @param UJM\ExoBundle\Entity\Exercise $exercise instance of Exercise
     * @param Doctrine EntityManager $em
     */
    public function addQuestionInExercise($inter, $exercise) {
        if ($exercise != null) {
            if ($this->isExerciseAdmin($exercise)) {
                $this->setExerciseQuestion($exercise, $inter);
            }
        }
    }

    /**
     * To control the User's rights to this question
     *
     * @access private
     *
     * @param integer $questionID id Question
     *
     * @return Doctrine Query Result
     */
    public function controlUserQuestion($questionID, $container, $em)
    {
        $user = $container->get('security.token_storage')->getToken()->getUser();

        $question = $em
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $questionID);

        return $question;
    }
}
