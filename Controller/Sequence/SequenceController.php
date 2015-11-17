<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UJM\ExoBundle\Entity\Exercise;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Description of SequenceController.
 */
class SequenceController extends Controller
{
    /**
     * Render the Exercise player main view.
     * This view instaciate the angular app
     * @Route("/play/{id}", requirements={"id" = "\d+"}, name="ujm_exercise_play", options={"expose"=true})
     * @Method("GET")
     */
    public function playAction(Exercise $exercise)
    {
        // TODO check authorisation
        /*$collection = new ResourceCollection([$exercise->getResourceNode()]);
        // $this->authorization : commenton le récupère ?
        if (!$this->authorization->isGranted('OPEN', $collection)) {
            throw new AccessDeniedHttpException();
        }*/
        // 
        // 
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        // get number of attempts already done by user
        $nbAttempts = $this->container->get('ujm.exo_exercise')->getNbPaper($user->getId(), $exercise->getId());

        $paperManager = $this->get('ujm.exo.paper_manager');
        $apiData = $paperManager->openPaper($exercise, $user, false);
        $exo = json_encode($apiData['exercise']);
        $paper = json_encode($apiData['paper']);
        
        //echo '<pre>';
        //print_r($apiData);die;
        
        return $this->render('UJMExoBundle:Sequence:play.html.twig', array(
                    '_resource' => $exercise,
                    'sequence' => $exo,
                    'attempts' => $nbAttempts,
                    'paper' => $paper,
                    'user' => $user->getId()
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
}
