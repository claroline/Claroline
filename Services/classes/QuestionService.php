<?php

/**
 *
 * Services for the questions
 * To display the badge obtained by an user in his list of copies
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Yaml\Parser;

class QuestionService {

    private $doctrine;
    private $tokenStorage;
    private $kernel;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection;
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Dependency Injection
     *
     */
    public function __construct(
            Registry $doctrine,
            TokenStorageInterface $tokenStorage,
            Kernel $kernel
    )
    {
        $this->doctrine     = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->kernel       = $kernel;
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
        $em   = $this->doctrine->getEntityManager();
        $user = $this->tokenStorage->getToken()->getUser();

        $questions = $em->getRepository('UJMExoBundle:Share')
                        ->getControlSharedQuestion($user->getId(), $questionID);

        return $questions;
    }

    /**
     *
     * Call after applied a filter in a questions list to know the actions allowed for each interaction
     *
     * @access public
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $listInteractions
     * @param integer $userID id User
     *
     * @return mixed[]
     */
    public function getActionsAllQuestions($listInteractions, $userID)
    {
        $em = $this->doctrine->getEntityManager();

        $allActions           = array();
        $actionQ              = array();
        $questionWithResponse = array();
        $alreadyShared        = array();
        $sharedWithMe         = array();
        $shareRight           = array();

        foreach ($listInteractions as $interaction) {
            if ($interaction->getQuestion()->getUser()->getId() == $userID) {
                $actionQ[$interaction->getQuestion()->getId()] = 1; // my question

                $actions = $this->getActionInteraction($interaction);
                $questionWithResponse += $actions[0];
                $alreadyShared += $actions[1];
            } else {
                $sharedQ = $em->getRepository('UJMExoBundle:Share')
                ->findOneBy(array('user' => $userID, 'question' => $interaction->getQuestion()->getId()));

                if (count($sharedQ) > 0) {
                    $actionQ[$interaction->getQuestion()->getId()] = 2; // shared question

                    $actionsS = $this->getActionShared($sharedQ);
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
     * To control the User's rights to this question
     *
     * @access public
     *
     * @param integer $questionID id Question
     *
     * @return Doctrine Query Result
     */
    public function controlUserQuestion($questionID)
    {
        $em   = $this->doctrine->getEntityManager();
        $user = $this->tokenStorage->getToken()->getUser();

        $question = $em
            ->getRepository('UJMExoBundle:Question')
            ->getControlOwnerQuestion($user->getId(), $questionID);

        return $question;
    }

    /**
     * For an interaction know if it's linked with response and if it's shared
     *
     * @access public
     *
     * @param \UJM\ExoBundle\Entity\Interaction $interaction
     *
     * @return boolean[]
     */
    public function getActionInteraction(\UJM\ExoBundle\Entity\Interaction $interaction)
    {
        $em = $this->doctrine->getEntityManager();
        $response = $em->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $interaction->getId()));
        if (count($response) > 0) {
            $questionWithResponse[$interaction->getId()] = 1;
        } else {
            $questionWithResponse[$interaction->getId()] = 0;
        }

        $share = $em->getRepository('UJMExoBundle:Share')
            ->findBy(array('question' => $interaction->getQuestion()->getId()));
        if (count($share) > 0) {
            $alreadyShared[$interaction->getQuestion()->getId()] = 1;
        } else {
            $alreadyShared[$interaction->getQuestion()->getId()] = 0;
        }

        $actions[0] = $questionWithResponse;
        $actions[1] = $alreadyShared;

        return $actions;
    }

    /**
     * For an shared interaction whith me, know if it's linked with response and if I can modify it
     *
     * @access public
     *
     * @param Doctrine EntityManager $em
     * @param \UJM\ExoBundle\Entity\Share $shared
     *
     * @return array
     */
    public function getActionShared($shared)
    {
        $em = $this->doctrine->getEntityManager();
        $inter = $em->getRepository('UJMExoBundle:Interaction')
                ->findOneBy(array('question' => $shared->getQuestion()->getId()));

        $sharedWithMe[$shared->getQuestion()->getId()] = $inter;
        $shareRight[$inter->getId()] = $shared->getAllowToModify();

        $response = $em->getRepository('UJMExoBundle:Response')
            ->findBy(array('interaction' => $inter->getId()));

        if (count($response) > 0) {
            $questionWithResponse[$inter->getId()] = 1;
        } else {
            $questionWithResponse[$inter->getId()] = 0;
        }

        $actionsS[0] = $sharedWithMe;
        $actionsS[1] = $shareRight;
        $actionsS[2] = $questionWithResponse;

        return $actionsS;
    }

    /**
     * Browse the file where are stored the various typical of interaction
     * @return array of type Interaction
     */
    public function getTypes()
    {
        $path = $this->kernel->locateResource('@UJMExoBundle');
        $yaml= new Parser();
        $interactionType=$yaml->parse(file_get_contents($path . 'Resources/config/interaction.yml'));
        return $interactionType;
    }

}
