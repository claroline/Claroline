<?php

namespace Innova\PathBundle\Controller\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Manager\UserProgressionManager;
use JMS\DiExtraBundle\Annotation as DI;
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
    /** @var UserProgressionManager */
    private $userProgressionManager;

    /**
     * PathController constructor.
     *
     * @DI\InjectParams({
     *     "userProgressionManager" = @DI\Inject("innova_path.manager.user_progression")
     * })
     *
     * @param UserProgressionManager $userProgressionManager
     */
    public function __construct(UserProgressionManager $userProgressionManager)
    {
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Open resource action.
     *
     * @EXT\Route("/{id}", name="innova_path_player_wizard")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     * @EXT\Template("InnovaPathBundle:Path:open.html.twig")
     *
     * @param Path $path
     * @param User $user
     *
     * @return array
     */
    public function openAction(Path $path, User $user = null)
    {
        $this->assertHasPermission('OPEN', $path);
        $resourceTypes = $this->hasPermission('EDIT', $path) ?
            $this->get('claroline.persistence.object_manager')
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findBy(['isEnabled' => true]) :
            [];
        $userEvaluation = !empty($user) ?
            $this->userProgressionManager->getUpdatedResourceUserEvaluation($path, $user) :
            null;

        return [
            '_resource' => $path,
            'resourceTypes' => array_map(function (ResourceType $resourceType) {
                return $this->get('claroline.serializer.resource_type')->serialize($resourceType);
            }, $resourceTypes),
            'userEvaluation' => $userEvaluation,
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
                $this->get('translator')->trans('publish_success', [], 'path')
            );
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('publish_error', [], 'path')
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

    private function hasPermission($permission, Path $path)
    {
        $collection = new ResourceCollection([$path->getResourceNode()]);

        return $this->get('security.authorization_checker')->isGranted($permission, $collection);
    }
}
