<?php

/**
 * Services for the questions
 * To display the badge obtained by an user in his list of copies.
 */

namespace UJM\ExoBundle\Services\classes;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SearchQuestionService {
    private $tokenStorage;
    private $doctrine;
    private $container;
    
    private $type;// In which column
    private $whatToFind;// Which text to find
    private $user;
    
    public function __construct(Registry $doctrine,ContainerInterface $container,TokenStorageInterface $tokenStorage) {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->request = $container->get('request');
        $this->tokenStorage = $tokenStorage;
        
        $this->user = $this->container->get('security.token_storage')->getToken()->getUser();
        $this->type = $this->request->query->get('type'); 
        $this->whatToFind = $this->request->query->get('whatToFind'); 
    }
    /**
     * return questions list by his type
     * @return array 
     */
    public function choiceTypeQuestion() {
        
        $em = $this->doctrine->getManager();
        $questionRepository = $em->getRepository('UJMExoBundle:Question');
        switch ($this->type) {
            case 'Category':
                $listQuestions = $questionRepository->findByUserAndCategoryName($this->user, $this->whatToFind);
                break;
            case 'Type':
                $listQuestions = $questionRepository->findByUserAndType($this->user, $this->whatToFind);
                break;
            case 'Title':
                $listQuestions = $questionRepository->findByUserAndTitle($this->user, $this->whatToFind);
                break;
            case 'Contain':
                $listQuestions = $questionRepository->findByUserAndInvite($this->user, $this->whatToFind);
                break;
            case 'All':
                $listQuestions = $questionRepository->findByUserAndContent($this->user, $this->whatToFind);
                break;
        }
        return $listQuestions;
    }
    /**
     * return questions shared list by his type
     * @return array
     */
    public function choiceTypeShare() {
        $em = $this->doctrine->getManager();
        $userID=$this->user->getId();
        $sharedQuestion = $em->getRepository('UJMExoBundle:Share');
        switch ($this->type) {
                    case 'Category':
                        $listeSharedQuestion=$sharedQuestion->findByCategoryShared($userID, $this->whatToFind);                   
                        break;
                    case 'Type':
                        $listeSharedQuestion=$sharedQuestion->findByTypeShared($userID, $this->whatToFind);
                        break;
                    case 'Title':
                        $listeSharedQuestion=$sharedQuestion->findByTitleShared($userID, $this->whatToFind);
                        break;
                    case 'Contain':
                        $listeSharedQuestion=$sharedQuestion->findByContainShared($userID, $this->whatToFind);
                        break;
                    case 'All':
                        $listeSharedQuestion=$sharedQuestion->findByAllShared($userID, $this->whatToFind);
                        break;
                }
        return $listQuestions= $listQuestions=$this->listQuestion($listeSharedQuestion);
    }

    /**
     * Return questions shared list
     * @param array $sharedQuestion //Result of questions shared list
     * @return array
     */
    private function listQuestion($sharedQuestion) {
        $listQuestions = array();
        $end = count($sharedQuestion);
        for ($i = 0; $i < $end; $i++) {
            $listQuestions[] = $sharedQuestion[$i]->getQuestion();
        }
        return $listQuestions;
    }
    /**
     * For all the matching questions search if the interaction is link to a paper (interaction in the test has already been passed)
     * @param array $listQuestions
     * @param string $nameEntity
     * @return int
     */
    public function searchEntityResponse($listQuestions, $nameEntity) {

        $em = $this->doctrine->getManager();
        $resultEnity = array();
        foreach ($listQuestions as $question) {
            $entity = $em->getRepository('UJMExoBundle:' . $nameEntity)
                    ->findOneByQuestion($question);

            if ($entity) {
                $resultEnity[$question->getId()] = 1;
            } else {
                $resultEnity[$question->getId()] = 0;
            }
        }
        return $resultEnity;
    }
 
}
