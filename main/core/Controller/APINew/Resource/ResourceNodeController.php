<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/resource")
 */
class ResourceNodeController extends AbstractCrudController
{
    /** @var ResourceManager */
    private $resourceManager;

    /** @var RightsManager */
    private $rightsManager;

    /**
     * ResourceNodeController constructor.
     *
     * @DI\InjectParams({
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager")
     * })
     *
     * @param ResourceManager $resourceManager
     * @param RightsManager   $rightsManager
     */
    public function __construct(
        ResourceManager $resourceManager,
        RightsManager $rightsManager
    ) {
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'resource_node';
    }

    /**
     * @EXT\Route("/{parent}", name="apiv2_resource_list", defaults={"parent"=null})
     *
     * @param Request $request
     * @param string  $parent
     * @param string  $class
     *
     * @return JsonResponse
     *
     * @todo do not return hidden resources to standard users
     */
    public function listAction(Request $request, $parent, $class = ResourceNode::class)
    {
        $options = $request->query->all();

        if (!empty($parent)) {
            // grab directory content
            $parentNode = $this->om
                ->getRepository(ResourceNode::class)
                ->findOneBy(['uuid' => $parent]);

            $options['hiddenFilters']['parent'] = $parentNode ? $parentNode->getId() : null;

            if ($parentNode) {
                $permissions = $this->rightsManager->getCurrentPermissionArray($parentNode);

                if (!isset($permissions['administrate']) || !$permissions['administrate']) {
                    $options['hiddenFilters']['published'] = true;
                }
            }
        } else {
            $options['hiddenFilters']['parent'] = null;
        }
        $options['hiddenFilters']['active'] = true;
        $options['hiddenFilters']['resourceTypeEnabled'] = true;

        //fix the very large list at the root
        //todo: when impersonating is fixed for easy testing, do that kind of stuff everywhere (not only at the root level)
        //directly in the finder
        //it currently work (altough we can see stuff we shouldnt do through the api)

        $roles = array_map(
            function ($role) { return $role->getRole(); },
            $this->container->get('security.token_storage')->getToken()->getRoles()
        );

        if (!in_array('ROLE_ADMIN', $roles) || $options['hiddenFilters']['parent'] === null) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)
        );
    }

    /**
     * @EXT\Route("/portal", name="apiv2_portal_index")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function portalSearchAction(Request $request)
    {
        $options = $request->query->all();

        $options['hiddenFilters']['published'] = true;

        // Limit the search to resource nodes published to portal
        $options['hiddenFilters']['publishedToPortal'] = true;

        // Limit the search to only the authorized resource types which can be displayed on the portal
        $options['hiddenFilters']['resourceType'] = $this->container->get('claroline.manager.portal_manager')->getPortalEnabledResourceTypes();

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)
        );
    }

    /**
     * @EXT\Route(
     *     "{parent}/files",
     *     name="apiv2_resource_files_create"
     * )
     * @EXT\ParamConverter(
     *     "parent",
     *     class="ClarolineCoreBundle:Resource\ResourceNode",
     *     options={"mapping": {"parent": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param ResourceNode $parent
     * @param User         $user
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function resourceFilesCreateAction(ResourceNode $parent, User $user, Request $request)
    {
        $filesData = $request->files->all();
        $files = isset($filesData['files']) ? $filesData['files'] : [];
        $handler = $request->get('handler');
        $publicFiles = [];
        $resources = [];
        /** @var StrictDispatcher */
        $dispatcher = $this->container->get('claroline.event.event_dispatcher');

        foreach ($files as $file) {
            $publicFile = $this->crud->create(
                'Claroline\CoreBundle\Entity\File\PublicFile',
                [],
                ['file' => $file]
            );
            $dispatcher->dispatch(strtolower('upload_file_'.$handler), 'File\UploadFile', [$publicFile]);
            $publicFiles[] = $publicFile;
        }
        $resourceType = $this->resourceManager->getResourceTypeByName('file');

        $this->om->startFlushSuite();

        foreach ($publicFiles as $publicFile) {
            $resource = new File();
            $resource->setName($publicFile->getFilename());
            $resource->setHashName($publicFile->getUrl());
            $resource->setMimeType($publicFile->getMimeType());
            $resource->setSize($publicFile->getSize());
            $resources[] = $this->resourceManager->create(
                $resource,
                $resourceType,
                $user,
                null,
                $parent
            );
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (File $file) {
            return $this->serializer->serialize($file->getResourceNode());
        }, $resources));
    }
}
