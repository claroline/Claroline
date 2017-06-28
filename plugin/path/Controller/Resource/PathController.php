<?php

namespace Innova\PathBundle\Controller\Resource;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Innova\PathBundle\Entity\Path\Path;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Path resource controller.
 * Exposes available actions for the resource.
 *
 * @EXT\Route("/paths", options={"expose"=true})
 */
class PathController extends Controller
{
    /**
     * Open resource action.
     *
     * @EXT\Route("/{id}", name="innova_path_player_wizard")
     * @EXT\Method("GET")
     * @EXT\Template("InnovaPathBundle:Path:open.html.twig")
     *
     * @param Path $path
     *
     * @return array
     */
    public function openAction(Path $path)
    {
        $this->assertHasPermission('OPEN', $path);

        return [
            '_resource' => $path,
            'editEnabled' => $this->get('innova_path.manager.path')->canEdit($path),
            'userProgression' => $this->get('innova_path.manager.user_progression')->getUserProgression($path),
            'totalProgression' => $this->get('innova_path.manager.user_progression')->calculateUserProgressionInPath($path),
        ];
    }

    /**
     * Edit resource action.
     *
     * @EXT\Route("/{id}/edit", name="innova_path_editor_wizard")
     * @EXT\Method("GET")
     * @EXT\Template("InnovaPathBundle:Path:edit.html.twig")
     *
     * @param Path $path
     *
     * @return array
     */
    public function editAction(Path $path)
    {
        $this->assertHasPermission('ADMINISTRATE', $path);

        return [
            '_resource' => $path,
        ];
    }

    /**
     * Display users progressions for a Path.
     *
     * @todo optimise queries
     *
     * @EXT\Route("/{id}/results", name="innova_path_manage_results")
     * @EXT\Method("GET")
     * @EXT\Template("InnovaPathBundle::manageResults.html.twig")
     *
     * @param Path $path
     *
     * @return array
     */
    public function manageResultsAction(Path $path)
    {
        $this->assertHasPermission('ADMINISTRATE', $path);

        // retrieve users having access to the WS
        $users = $this->get('claroline.persistence.object_manager')->getRepository('ClarolineCoreBundle:User')->findUsersByWorkspace($path->getWorkspace());
        $results = array_map(function (User $user) use ($path) {
            return [
                'user' => $user,
                'progression' => $this->get('innova_path.manager.user_progression')->getUserProgression($path, $user),
                'locked' => $this->get('innova_path.manager.path')->getPathLockedProgression($path),
            ];
        }, $users);

        return [
            '_resource' => $path,
            'results' => $results,
        ];
    }

    /**
     * Publish a Path.
     *
     * @EXT\Route("/{id}/publish", name="innova_path_publish")
     * @EXT\Method("GET")
     *
     * @param Path    $path
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function publishAction(Path $path, Request $request)
    {
        $this->assertHasPermission('ADMINISTRATE', $path);

        try {
            $this->get('innova_path.manager.publishing')->publish($path);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('publish_success', [], 'path_wizards')
            );
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('publish_error', [], 'path_wizards')
            );
        }

        return new RedirectResponse($request->headers->get('referer'));
    }

    private function assertHasPermission($permission, Path $path)
    {
        $collection = new ResourceCollection([$path->getResourceNode()]);

        if (!$this->get('security.authorization_checker')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
