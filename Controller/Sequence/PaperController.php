<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use UJM\ExoBundle\Entity\Exercise;

/**
 * PaperController.
 */
class PaperController extends Controller
{
    /**
     * Render the paper list view.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="ujm_exercice_papers", options={"expose"=true})
     * @Method("GET")
     */
    public function exercisePapersAction(Exercise $exercise)
    {
        // get user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        return $this->render('UJMExoBundle:Sequence:papers.html.twig', array(
                    '_resource' => $exercise
                        )
        );
    }    
}
