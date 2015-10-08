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
    
    public function __construct(Registry $doctrine,ContainerInterface $container,TokenStorageInterface $tokenStorage) {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->request = $container->get('request');
        $this->tokenStorage = $tokenStorage;        
    }
    /**
     * 
     * @param string $repository Name of repository called (Question or Share)
     * @return array
     */
    public function choiceTypeQuestion($repository) {
        $type = $this->request->query->get('type');
        $whatToFind = $this->request->query->get('whatToFind');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $em = $this->doctrine->getManager();
        $questionRepository = $em->getRepository('UJMExoBundle:'.$repository);
        
        switch ($type) {
            case 'Category':
                $listQuestions = $questionRepository->findByUserAndCategoryName($user, $whatToFind);
                break;
            case 'Type':
                $listQuestions = $questionRepository->findByUserAndType($user, $whatToFind);
                break;
            case 'Title':
                $listQuestions = $questionRepository->findByUserAndTitle($user, $whatToFind);
                break;
            case 'Contain':
                $listQuestions = $questionRepository->findByUserAndInvite($user, $whatToFind);
                break;
            case 'All':
                $listQuestions = $questionRepository->findByUserAndContent($user, $whatToFind);
                break;
        }
        return $listQuestions;
    }

    /**
     * Return questions shared list
     * @param array $sharedQuestion //Result of questions shared list
     * @return array
     */
    public function listQuestion($sharedQuestion) {
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
