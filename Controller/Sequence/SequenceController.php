<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use UJM\ExoBundle\Entity\Exercise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Description of SequenceController.
 */
class SequenceController extends Controller
{
    /**
     * Render the Exercise player main view.
     * This view instaciate the angular PlayerApp
     * @Route("/play/{id}", requirements={"id" = "\d+"}, name="ujm_exercise_play", options={"expose"=true})
     * @Method("GET")
     */
    public function playAction(Exercise $exercise)
    {
        // check authorisation
        $collection = new ResourceCollection([$exercise->getResourceNode()]);
        // $this->authorization : commenton le récupère ?
        if (!$this->container->get('security.authorization_checker')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedHttpException();
        }
        
        return $this->render('UJMExoBundle:Sequence:play.html.twig', array(
                    '_resource' => $exercise
            )
        );
    }

    
    
    
    /**
     * handle AngularServices errors
     * @Route("/error/", name="ujm_sequence_error", options={"expose"=true})
     * @Method("GET")
     */
    public function sequenceError(Request $request)
    {
        $message = $request->get('message');
        $code = $request->get('code');
        switch ($code){
            case '403':
                throw new AccessDeniedHttpException($message);
                break;
            default :
                 throw new NotFoundHttpException($code . ' ' . $message);
        }
       
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
    
    /**
     *  @Route("/exercise/{id}/user", name="sequence_get_connected_user", options={"expose"=true})
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
}
