<?php

/**
 * Services for the questions
 * To display the badge obtained by an user in his list of copies.
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Yaml\Parser;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Exercise;
use Claroline\CoreBundle\Entity\User;

class QuestionService {

    private $doctrine;
    private $tokenStorage;
    private $kernel;

    /**
     * Constructor.
     *
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry                                            $doctrine     Dependency Injection;
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     */
    public function __construct(
    Registry $doctrine, TokenStorageInterface $tokenStorage, Kernel $kernel
    ) {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->kernel = $kernel;
    }

    /**
     * To control the User's rights to this shared question.
     *
     *
     * @param int $questionID id Question
     *
     * @return array
     */
    public function controlUserSharedQuestion($questionID) {
        $em = $this->doctrine->getEntityManager();
        $user = $this->tokenStorage->getToken()->getUser();

        $questions = $em->getRepository('UJMExoBundle:Share')
                ->getControlSharedQuestion($user->getId(), $questionID);

        return $questions;
    }

    /**
     * Call after applied a filter in a questions list to know the actions allowed for each interaction.
     *
     * @param Collection of \UJM\ExoBundle\Entity\Question  $listQuestions
     * @param int                                           $userID
     *
     * @return mixed[]
     */
    public function getActionsAllQuestions($listQuestions, $userID) {
        $em = $this->doctrine->getEntityManager();

        $allActions = array();
        $actionQ = array();
        $questionWithResponse = array();
        $alreadyShared = array();
        $sharedWithMe = array();
        $shareRight = array();

        foreach ($listQuestions as $question) {
            if ($question->getUser()->getId() == $userID) {
                $actionQ[$question->getId()] = 1; // my question

                $actions = $this->getActionQuestion($question);
                $questionWithResponse += $actions[0];
                $alreadyShared += $actions[1];
            } else {
                $sharedQ = $em->getRepository('UJMExoBundle:Share')
                        ->findOneBy(array('user' => $userID, 'question' => $question->getId()));

                if ($sharedQ) {
                    $actionQ[$question->getId()] = 2; // shared question

                    $actionsS = $this->getActionShared($sharedQ);
                    $sharedWithMe += $actionsS[0];
                    $shareRight += $actionsS[1];
                    $questionWithResponse += $actionsS[2];
                } else {
                    $actionQ[$question->getId()] = 3; // other
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
     * To control the User's rights to this question.
     *
     *
     * @param int $questionId
     *
     * @return mixed
     */
    public function controlUserQuestion($questionId) {
        $em = $this->doctrine->getEntityManager();
        $user = $this->tokenStorage->getToken()->getUser();

        return $em->getRepository('UJMExoBundle:Question')
                        ->findOneBy(['id' => $questionId, 'user' => $user]);
    }

    /**
     * For a question know if it's linked with response and if it's shared
     *
     * @param Question $question
     * @return boolean[]
     */
    public function getActionQuestion(Question $question) {
        $em = $this->doctrine->getEntityManager();
        $response = $em->getRepository('UJMExoBundle:Response')
                ->findOneByQuestion($question);

        if ($response) {
            $questionWithResponse[$question->getId()] = 1;
        } else {
            $questionWithResponse[$question->getId()] = 0;
        }

        $share = $em->getRepository('UJMExoBundle:Share')
                ->findOneByQuestion($question);

        if ($share) {
            $alreadyShared[$question->getId()] = 1;
        } else {
            $alreadyShared[$question->getId()] = 0;
        }

        $actions[0] = $questionWithResponse;
        $actions[1] = $alreadyShared;

        return $actions;
    }

    /**
     * For an shared interaction whith me, know if it's linked with response and if I can modify it.
     *
     *
     * @param Doctrine EntityManager      $em
     * @param \UJM\ExoBundle\Entity\Share $shared
     *
     * @return array
     */
    public function getActionShared($shared) {
        $em = $this->doctrine->getEntityManager();
        $question = $shared->getQuestion();

        $sharedWithMe[$shared->getQuestion()->getId()] = $question;
        $shareRight[$question->getId()] = $shared->getAllowToModify();

        $response = $em->getRepository('UJMExoBundle:Response')
                ->findOneByQuestion($question);

        if ($question) {
            $questionWithResponse[$question->getId()] = 1;
        } else {
            $questionWithResponse[$question->getId()] = 0;
        }

        $actionsS[0] = $sharedWithMe;
        $actionsS[1] = $shareRight;
        $actionsS[2] = $questionWithResponse;

        return $actionsS;
    }

    /**
     * Browse the file where are stored the various typical of interaction.
     *
     * @return array of type Interaction
     */
    public function getTypes() {
        $path = $this->kernel->locateResource('@UJMExoBundle');
        $yaml = new Parser();
        $interactionType = $yaml->parse(file_get_contents($path . 'Resources/config/interaction.yml'));

        return $interactionType;
    }
    /**
     * 
     * @param type $idExo
     * @param type $user
     * @param Exercise $exercise
     * @return type
     */
    public function getListQuestionExo($idExo,User $user, Exercise $exercise) {
        if ($idExo == -2) {
            $listQExo = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Question')
                    ->findByUserNotInExercise($user, $exercise, true);
        } else {
            $listQExo = $this->getDoctrine()
                    ->getManager()
                    ->getRepository('UJMExoBundle:Question')
                    ->findByExercise($exercise);
        }

        return $listQExo;
    }
     /**
     * Question shared with user
     * @param array $shared
     * @return array
     */
    public function getQuestionShare($shared){
        $sharedWithMe = array();

                $end = count($shared);

                for ($i = 0; $i < $end; $i++) {
                    $sharedWithMe[] = $shared[$i]->getQuestion();
                }
                return $sharedWithMe;
    }

}
