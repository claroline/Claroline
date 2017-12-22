<?php

namespace UJM\ExoBundle\Controller\Resource;

use Claroline\CoreBundle\Entity\User;
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
     * Opens an exercise.
     *
     * @EXT\Route("", name="ujm_exercise_open")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Template("UJMExoBundle:Exercise:open.html.twig")
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return array
     */
    public function openAction(Exercise $exercise, User $user = null)
    {
        $this->assertHasPermission('OPEN', $exercise);

        $nbUserPapers = 0;
        $nbUserPapersDayCount = 0;

        if ($user instanceof User) {
            $nbUserPapers = $this->container->get('ujm_exo.manager.paper')->countUserFinishedPapers($exercise, $user);
            $nbUserPapersDayCount = $this->container->get('ujm_exo.manager.paper')->countUserFinishedDayPapers($exercise, $user);
        }

        // TODO : no need to count the $nbPapers for regular users as it's only for admins
        $nbPapers = $this->container->get('ujm_exo.manager.paper')->countExercisePapers($exercise);
        $exerciseData = $this->get('ujm_exo.manager.exercise')->serialize(
            $exercise,
            $this->canEdit($exercise) ? [Transfer::INCLUDE_SOLUTIONS] : []
        );

        // TODO: the following data should be included directly by the manager/serializer
        $exerciseData->meta->paperCount = (int) $nbPapers;
        $exerciseData->meta->userPaperCount = (int) $nbUserPapers;
        $exerciseData->meta->userPaperDayCount = (int) $nbUserPapersDayCount;
        $exerciseData->meta->registered = $user instanceof User;

        return [
            '_resource' => $exercise,
            'exercise' => $exerciseData,
        ];
    }

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

    private function canEdit(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->get('security.authorization_checker')->isGranted('EDIT', $collection);
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
