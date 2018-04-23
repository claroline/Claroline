<?php

namespace UJM\ExoBundle\Controller\Resource;

use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Exercise Controller renders views.
 *
 * @EXT\Route("/exercises/{id}", options={"expose"=true})
 * @EXT\ParamConverter("exercise", class="UJMExoBundle:Exercise", options={"mapping": {"id": "uuid"}})
 */
class ExerciseController extends Controller
{
    /**
     * To display the docimology's histograms.
     *
     * @EXT\Route("/docimology", name="ujm_exercise_docimology")
     * @EXT\Method("GET")
     * @EXT\Template("UJMExoBundle:Exercise:docimology.html.twig")
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function docimologyAction(Exercise $exercise)
    {
        $this->assertHasPermission('VIEW_DOCIMOLOGY', $exercise);

        return [
            '_resource' => $exercise,
            'exercise' => $this->get('ujm_exo.manager.exercise')->serialize($exercise, [Transfer::MINIMAL]),
            'statistics' => $this->get('ujm_exo.manager.docimology')->getStatistics($exercise, 100),
        ];
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
