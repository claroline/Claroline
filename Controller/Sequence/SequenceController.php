<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UJM\ExoBundle\Entity\Exercise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;

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
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();   

        $paperManager = $this->get('ujm.exo.paper_manager');
        $apiData = $paperManager->openPaper($exercise, $user, false);
        $exo = json_encode($apiData['exercise']);
        $paper = json_encode($apiData['paper']);
        
        $u = array(
            'id' => $user->getId(),
            'name' => $user->getFirstName() . '' . $user->getLastName(),
            'admin' => $this->isExerciseAdmin($exercise)
        );


        
        return $this->render('UJMExoBundle:Sequence:play.html.twig', array(
                    '_resource' => $exercise,
                    'sequence' => $exo,
                    'paper' => $paper,
                    'user' => json_encode($u)
            )
        );
    }

    /**
     * @Route("error", name="ujm_sequence_error", options={"expose"=true})
     * @Method("GET")
     */
    public function sequenceError()
    {
        throw new NotFoundHttpException();
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
