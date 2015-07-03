<?php

namespace UJM\ExoBundle\Controller\Player;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use UJM\ExoBundle\Entity\Player\ExercisePlayer;

/**
 * Description of ExercisePlayerController
 *
 * @author patrick
 */
class ExercisePlayerController extends Controller{
   
    
    
    /**
     * display an exercise player
     * @Route("/view/{id}", requirements={"id" = "\d+"}, name="ujm_player_open")
     * @Method("GET")
     * @ParamConverter("ExercisePlayer", class="UJMExoBundle:Player\ExercisePlayer")
     */
    public function openAction(ExercisePlayer $resource) {
        if (false === $this->container->get('security.context')->isGranted('OPEN', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }
        return $this->render('UJMExoBundle:Player:view.html.twig', array(
                    '_resource' => $resource
            )
        );
    }
    
    /**
     * administrate an exercise player
     * @Route("/edit/{id}", requirements={"id" = "\d+"}, name="ujm_player_administrate")
     * @Method("GET")
     * @ParamConverter("ExercisePlayer", class="UJMExoBundle:Player\ExercisePlayer")
     */
    public function administrateAction(ExercisePlayer $resource) {
        if (false === $this->container->get('security.context')->isGranted('ADMINISTRATE', $resource->getResourceNode())) {
            throw new AccessDeniedException();
        }
        return $this->render('UJMExoBundle:Player:edit.html.twig', array(
                    '_resource' => $resource
            )
        );
    }
}
