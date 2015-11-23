<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UJM\ExoBundle\Entity\Exercise;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;

/**
 * PaperController.
 */
class PaperController extends Controller
{
    /**
     * Instanciate Angular PapersApp
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="ujm_exercice_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function exercisePapersAction(Exercise $exercise)
    {
       

        return $this->render('UJMExoBundle:Sequence:papers.html.twig', array(
                    '_resource' => $exercise
                        )
        );
    }
    
    /**
     *  @Route("/exercise/{id}/user", name="paper_get_connected_user", options={"expose"=true})
     */
    public function getCurrentUser(Exercise $exercise){        
         // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        
         
        $u = array(
            'id' => $user->getId(),
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'admin' => $this->isExerciseAdmin($exercise)
        );       
        
        return new JsonResponse($u);
        
    }
    
    private function isExerciseAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection(array($exercise->getResourceNode())); 
        if ( $this->container->get('security.authorization_checker')->isGranted('ADMINISTRATE', $collection)) {
            return true;
        } else {
            return false;
        }
    }
}
