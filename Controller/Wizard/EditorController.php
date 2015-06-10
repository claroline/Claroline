<?php

namespace Innova\PathBundle\Controller\Wizard;

use Innova\PathBundle\Form\Handler\PathHandler;
use Innova\PathBundle\Manager\PathManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Innova\PathBundle\Entity\Path\Path;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;

/**
 * Class EditorController
 *
 * @Route(
 *      "/editor",
 *      name    = "innova_path_editor",
 *      service = "innova_path.controller.path_editor"
 * )
 */
class EditorController
{
    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Router
     * @var \Symfony\Component\Routing\RouterInterface $router
     */
    protected $router;

    /**
     * Form factory
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;

    /**
     * Resource manager
     * @var \Claroline\CoreBundle\Manager\ResourceManager
     */
    protected $resourceManager;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Claroline\CoreBundle\Manager\ResourceManager $resourceManager
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     * @param \Innova\PathBundle\Form\Handler\PathHandler $pathHandler
     */
    public function __construct(
        ObjectManager        $objectManager,
        RouterInterface      $router,
        FormFactoryInterface $formFactory,
        ResourceManager      $resourceManager,
        PathManager          $pathManager,
        PathHandler          $pathHandler)
    {
        $this->om              = $objectManager;
        $this->router          = $router;
        $this->formFactory    = $formFactory;
        $this->resourceManager = $resourceManager;
        $this->pathManager     = $pathManager;
        $this->pathHandler     = $pathHandler;
    }

    /**
     * Display Path Editor
     * @Route(
     *      "/{id}",
     *      name    = "innova_path_editor_wizard",
     *      options = { "expose" = true }
     * )
     * @Template("InnovaPathBundle:Wizard:editor.html.twig")
     */
    public function displayAction(Path $path)
    {
        // Check User credentials
        $this->pathManager->checkAccess('EDIT', $path);

        $resourceIcons = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findByIsShortcut(false);

        return array (
            '_resource'     => $path,
            'resourceIcons' => $resourceIcons,
        );
    }

    /**
     * Save Path
     * @param Path $path
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *      "/{id}",
     *      name    = "innova_path_editor_wizard_save",
     *      options = { "expose" = true }
     * )
     * @Method("PUT")
     */
    public function saveAction(Path $path)
    {
        $this->pathManager->checkAccess('EDIT', $path);

        // Create form
        $form = $this->formFactory->create('innova_path', $path, array (
            'method' => 'PUT',
            'csrf_protection' => false,
        ));

        $response = array ();

        // Try to process data
        $this->pathHandler->setForm($form);
        if ($this->pathHandler->process()) {
            $data = $this->pathHandler->getData();

            // Validation OK
            $response['status']   = 'OK';
            $response['messages'] = array ();
            $response['data']     = json_decode($data->getStructure());
        } else {
            // Validation Error
            $response['status']   = 'ERROR_VALIDATION';
            $response['messages'] = $this->pathHandler->getFormErrors();
            $response['data']     = null;
        }

        return new JsonResponse($response);
    }

    /**
     * Load activity data from ResourceNode id
     * @param  integer $nodeId
     * @return JsonResponse
     *
     * @Route(
     *      "/load_activity/{nodeId}",
     *      name         = "innova_path_load_activity",
     *      requirements = { "id"     = "\d+" },
     *      options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function loadActivityAction($nodeId)
    {
        $activity = array();

        $node = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($nodeId);
        if (!empty($node)) {
            $resource = $this->resourceManager->getResourceFromNode($node);
            if (!empty($resource)) {
                $activity['id'] = $resource->getId();
                $activity['name'] = $resource->getTitle();
                $activity['description'] = $resource->getDescription();

                // Primary resources
                $activity['primaryResource'] = null;
                $primaryResource = $resource->getPrimaryResource();
                if (!empty($primaryResource)) {
                    $activity['primaryResource'] = array(
                        'resourceId' => $primaryResource->getId(),
                        'name'       => $primaryResource->getName(),
                        'type'       => $primaryResource->getResourceType()->getName(),
                        'mimeType'   => $primaryResource->getMimeType(),
                    );
                }

                // Process activity parameters
                $parameters = $resource->getParameters();
                if (!empty($parameters)) {
                    // Secondary resources
                    $activity['resources'] = array();

                    $secondaryResources = $parameters->getSecondaryResources();
                    if (!empty($secondaryResources)) {
                        foreach ($secondaryResources as $secondaryResource) {
                            $activity['resources'][] = array(
                                'resourceId'          => $secondaryResource->getId(),
                                'name'                => $secondaryResource->getName(),
                                'type'                => $secondaryResource->getResourceType()->getName(),
                                'mimeType'            => $secondaryResource->getMimeType(),
                                'propagateToChildren' => true,
                            );
                        }
                    }

                    // Global Parameters
                    $activity['withTutor'] = $parameters->isWithTutor();
                    $activity['who']       = $parameters->getWho();
                    $activity['where']     = $parameters->getWhere();
                    $activity['duration']  = $parameters->getMaxDuration(); // Duration in seconds
                }
            }
        }

        return new JsonResponse($activity);
    }

    /**
     * Redirect to Activity using Activity ID
     * @param integer $activityId
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return RedirectResponse
     *
     * @Route(
     *      "/show_activity/{activityId}",
     *      name         = "innova_path_show_activity",
     *      requirements = { "id"     = "\d+" },
     *      options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function showActivityAction($activityId)
    {
        // Retrieve node from Activity id
        $activity = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity')->findOneById($activityId);
        if (empty($activity)) {
            throw new NotFoundHttpException('Unable to find Activity referenced by ID : ' . $activityId);
        }

        $route = $this->router->generate('claro_resource_open', array(
            'node'         => $activity->getResourceNode()->getId(),
            'resourceType' => 'activity'
        ));

        return new RedirectResponse($route);
    }
}
