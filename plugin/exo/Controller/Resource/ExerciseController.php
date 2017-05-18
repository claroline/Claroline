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
     * @param Exercise $exercise
     * @param User     $user
     *
     * @EXT\Route("", name="ujm_exercise_open")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Template("UJMExoBundle:Exercise:open.html.twig")
     *
     * @return array
     */
    public function openAction(Exercise $exercise, User $user = null)
    {
        $this->assertHasPermission('OPEN', $exercise);

        $nbUserPapers = 0;

        if ($user instanceof User) {
            $nbUserPapers = $this->container->get('ujm_exo.manager.paper')->countUserFinishedPapers($exercise, $user);
        }

        // TODO : no need to count the $nbPapers for regular users as it's only for admins
        $nbPapers = $this->container->get('ujm_exo.manager.paper')->countExercisePapers($exercise);
        $isAdmin = $this->isAdmin($exercise);
        $exerciseData = $this->get('ujm_exo.manager.exercise')->serialize(
            $exercise,
            $isAdmin ? [Transfer::INCLUDE_SOLUTIONS] : []
        );

        // TODO: the following data should be included directly by the manager/serializer
        $exerciseData->meta->editable = $isAdmin;
        $exerciseData->meta->paperCount = (int) $nbPapers;
        $exerciseData->meta->userPaperCount = (int) $nbUserPapers;
        $exerciseData->meta->registered = $user instanceof User;
        $exerciseData->meta->canViewPapers = $this->canViewPapers($exercise);
        $exerciseData->meta->canViewDocimology = $this->canViewDocimology($exercise);

        // Display the Summary of the Exercise
        return [
            // Used to build the Claroline Breadcrumbs
            'workspace' => $exercise->getResourceNode()->getWorkspace(),
            '_resource' => $exercise,
            'exercise' => $exerciseData,
            'editEnabled' => $isAdmin,
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
        if (!$this->canViewDocimology($exercise)) {
            throw new AccessDeniedException('not allowed to access this page');
        }

        return [
            'workspace' => $exercise->getResourceNode()->getWorkspace(),
            '_resource' => $exercise,
            'exercise' => $this->get('ujm_exo.manager.exercise')->serialize($exercise, [Transfer::MINIMAL]),
            'statistics' => $this->get('ujm_exo.manager.docimology')->getStatistics($exercise, 100),
        ];
    }

    private function isAdmin(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->get('security.authorization_checker')->isGranted('ADMINISTRATE', $collection);
    }

    private function canViewPapers(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        return $this->get('security.authorization_checker')->isGranted('MANAGE_PAPERS', $collection);
    }

    private function canViewDocimology(Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);
        $isGranted = $this->get('security.authorization_checker')->isGranted('VIEW_DOCIMOLOGY', $collection) || $this->isAdmin($exercise);

        return $isGranted;
    }

    private function assertHasPermission($permission, Exercise $exercise)
    {
        $collection = new ResourceCollection([$exercise->getResourceNode()]);

        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
