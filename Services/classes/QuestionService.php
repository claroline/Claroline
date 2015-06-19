<?php

/**
 *
 * Services for the questions
 * To display the badge obtained by an user in his list of copies
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class QuestionService {

    private $doctrine;
    protected $tokenStorage;

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
            TokenStorageInterface $tokenStorage
    )
    {
        $this->doctrine     = $doctrine;
        $this->tokenStorage = $tokenStorage;
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
     * Get information if these categories are linked to questions, allow to know if a category can be deleted or not
     *
     * @access public
     *
     * @return boolean[]
     */
    public function getLinkedCategories()
    {
        $em = $this->doctrine->getEntityManager();
        $linkedCategory = array();
        $repositoryCategory = $em->getRepository('UJMExoBundle:Category');

        $repositoryQuestion = $em->getRepository('UJMExoBundle:Question');

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
     *
     * Call after applied a filter in a questions list to know the actions allowed for each interaction
     *
     * @access public
     *
     * @param Collection of \UJM\ExoBundle\Entity\Interaction $listInteractions
     * @param integer $userID id User
     *
     * @return array
     */
    public function getActionsAllQuestions($listInteractions, $userID)
    {
        $em = $this->doctrine->getEntityManager();
        $interServ= $this->container->get('ujm.exo_Interaction_general');

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

}
