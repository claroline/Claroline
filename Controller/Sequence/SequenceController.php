<?php

namespace UJM\ExoBundle\Controller\Sequence;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use UJM\ExoBundle\Entity\Sequence\Sequence;
use UJM\ExoBundle\Entity\Exercise;

/**
 * Description of SequenceController.
 */
class SequenceController extends Controller
{
    /**
     * Play the selected Exercise.
     *
     * @Route("/play/{id}", requirements={"id" = "\d+"}, name="ujm_exercise_play")
     * @ParamConverter("Exercise", class="UJMExoBundle:Exercise")
     */
    public function playAction(Exercise $exercise)
    {
        // get api manager
        $manager = $this->get('ujm.exo.api_manager');
        $exo = $manager->exportExercise($exercise);

        $steps = $exo['steps'];
        $data = json_encode($exo);

        return $this->render('UJMExoBundle:Sequence:play.html.twig', array(
            '_resource' => $exercise, 
            'steps' => json_encode($steps),
            'sequence' => $data
                )
        );
    }
}
