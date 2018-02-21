<?php

namespace Innova\PathBundle\Controller\Api;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Form\Type\PathType;
use Innova\PathBundle\Manager\PathManager;
use Innova\PathBundle\Manager\PublishingManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Path API Controller exposes REST API.
 *
 * @todo use HTTP status code for error handling
 *
 * @EXT\Route("/paths", options={"expose"=true})
 */
class PathController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * @var PathManager
     */
    private $pathManager;

    /**
     * @var PublishingManager
     */
    private $publishingManager;

    /**
     * PathController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"     = @DI\Inject("security.authorization_checker"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"            = @DI\Inject("router"),
     *     "formFactory"       = @DI\Inject("form.factory"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "pathManager"       = @DI\Inject("innova_path.manager.path"),
     *     "publishingManager" = @DI\Inject("innova_path.manager.publishing")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param RouterInterface               $router
     * @param FormFactoryInterface          $formFactory
     * @param ResourceManager               $resourceManager
     * @param PathManager                   $pathManager
     * @param PublishingManager             $publishingManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ResourceManager $resourceManager,
        PathManager $pathManager,
        PublishingManager $publishingManager)
    {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->resourceManager = $resourceManager;
        $this->pathManager = $pathManager;
        $this->publishingManager = $publishingManager;
    }

    /**
     * Update a Path.
     *
     * @EXT\Route("/{id}", name="innova_path_editor_wizard_save")
     * @EXT\Method("PUT")
     *
     * @param Path    $path
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function updateAction(Path $path, Request $request)
    {
        $this->assertHasPermission('ADMINISTRATE', $path);

        // Create form
        $form = $this->formFactory->create(new PathType(), $path, [
            'method' => 'PUT',
            'csrf_protection' => false,
        ]);

        $response = [];

        // Try to process data
        $form->handleRequest($request);
        if ($form->isValid()) {
            // Form is valid => create or update the path
            $this->pathManager->edit($path);

            // Validation OK
            $response['status'] = 'OK';
            $response['messages'] = [];
            $response['data'] = $path->getStructure();
        } else {
            // Validation Error
            $response['status'] = 'ERROR_VALIDATION';
            $response['messages'] = $this->getFormErrors($form);
            $response['data'] = null;
        }

        return new JsonResponse($response);
    }

    /**
     * @EXT\Route("/{id}/publish", name="innova_path_publish_api")
     * @EXT\Method("PUT")
     *
     * @param Path $path
     *
     * @return JsonResponse
     */
    public function publishAction(Path $path)
    {
        $this->assertHasPermission('ADMINISTRATE', $path);

        try {
            $this->publishingManager->publish($path);

            return new JsonResponse([
                'status' => 'OK',
                'messages' => [],
                'data' => json_decode($path->getStructure()),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'ERROR',
                'messages' => [$e->getMessage()],
                'data' => null,
            ]);
        }
    }

    /**
     * Load activity data from ResourceNode id.
     *
     * @EXT\Route("/load_activity/{nodeId}", name="innova_path_load_activity")
     * @EXT\Method("GET")
     *
     * @param int $nodeId
     *
     * @return JsonResponse
     */
    public function loadActivityAction($nodeId)
    {
        $activity = [];

        /** @var ResourceNode $node */
        $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($nodeId);
        if (!empty($node)) {
            /** @var Activity $resource */
            $resource = $this->resourceManager->getResourceFromNode($node);
            if (!empty($resource)) {
                $activity['id'] = $resource->getId();
                $activity['name'] = $resource->getTitle();
                $activity['description'] = $resource->getDescription();

                // Primary resources
                $activity['primaryResource'] = null;

                /** @var ResourceNode $primaryResource */
                $primaryResource = $resource->getPrimaryResource();
                if (!empty($primaryResource)) {
                    $activity['primaryResource'] = [
                        'resourceId' => $primaryResource->getId(),
                        'name' => $primaryResource->getName(),
                        'type' => $primaryResource->getResourceType()->getName(),
                        'mimeType' => $primaryResource->getMimeType(),
                    ];
                }

                // Process activity parameters
                $parameters = $resource->getParameters();
                if (!empty($parameters)) {
                    // Secondary resources
                    $activity['resources'] = [];

                    $secondaryResources = $parameters->getSecondaryResources();
                    if (!empty($secondaryResources)) {
                        foreach ($secondaryResources as $secondaryResource) {
                            $activity['resources'][] = [
                                'resourceId' => $secondaryResource->getId(),
                                'name' => $secondaryResource->getName(),
                                'type' => $secondaryResource->getResourceType()->getName(),
                                'mimeType' => $secondaryResource->getMimeType(),
                                'propagateToChildren' => true,
                            ];
                        }
                    }

                    // Global Parameters
                    $activity['withTutor'] = $parameters->isWithTutor();
                    $activity['who'] = $parameters->getWho();
                    $activity['where'] = $parameters->getWhere();
                    $activity['duration'] = $parameters->getMaxDuration(); // Duration in seconds
                    $activity['evaluationType'] = $parameters->getEvaluationType(); //manual/automatic
                }
            }
        }

        return new JsonResponse($activity);
    }

    /**
     * Redirect to Activity using Activity ID.
     *
     * @EXT\Route("/show_activity/{activityId}", name="innova_path_show_activity")
     * @EXT\Method("GET")
     *
     * @param int $activityId
     *
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse
     */
    public function showActivityAction($activityId)
    {
        // Retrieve node from Activity id
        $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->find($activityId);
        if (empty($activity)) {
            throw new NotFoundHttpException('Unable to find Activity referenced by ID : '.$activityId);
        }

        $route = $this->router->generate('claro_resource_open', [
            'node' => $activity->getResourceNode()->getId(),
            'resourceType' => 'activity',
        ]);

        return new RedirectResponse($route);
    }

    /**
     * @param $form
     *
     * @return array
     */
    private function getFormErrors(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $errors[$key] = $error->getMessage();
        }

        // Get errors from children
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getFormErrors($child);
            }
        }

        return $errors;
    }

    private function assertHasPermission($permission, Path $path)
    {
        $collection = new ResourceCollection([$path->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
