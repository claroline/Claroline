<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Exception\ResourceAccessException;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
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

    /** @var ResourceActionManager */
    private $actionManager;

    /**
     * ResourceNodeController constructor.
     *
     * @param ResourceActionManager $actionManager
     * @param ResourceManager       $resourceManager
     * @param RightsManager         $rightsManager
     */
    public function __construct(
        ResourceActionManager $actionManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager
    ) {
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->actionManager = $actionManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'resource_node';
    }

    /**
     * @EXT\Route("/{parent}/{all}", name="apiv2_resource_list", defaults={"parent"=null}, requirements={"all": "all"})
     *
     * @param Request $request
     * @param string  $parent
     * @param string  $all
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, $parent, $all = null, $class = ResourceNode::class)
    {
        $options = $request->query->all();

        if ($parent) {
            // grab directory content
            /** @var ResourceNode $parentNode */
            $parentNode = $this->finder->get(ResourceNode::class)->findOneBy(['uuid_or_slug' => $parent]);

            if ($all) {
                $options['hiddenFilters']['path.after'] = $parentNode ? $parentNode->getPath() : null;
            } else {
                $options['hiddenFilters']['parent'] = $parentNode ? $parentNode->getUuid() : null;
            }

            if ($parentNode) {
                $permissions = $this->rightsManager->getCurrentPermissionArray($parentNode);

                if (!isset($permissions['administrate']) || !$permissions['administrate']) {
                    $options['hiddenFilters']['published'] = true;
                    $options['hiddenFilters']['hidden'] = false;
                }
            }
        } elseif (!$all) {
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

        if (!in_array('ROLE_ADMIN', $roles) || empty($options['hiddenFilters']['parent'])) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)
        );
    }

    /**
     * @EXT\Route(
     *     "/{parent}/files",
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
        $attributes['type'] = 'file';
        $collection = new ResourceCollection([$parent]);
        $collection->setAttributes($attributes);

        $add = $this->actionManager->get($parent, 'add');

        if (!$this->actionManager->hasPermission($add, $collection)) {
            throw new ResourceAccessException($collection->getErrorsForDisplay(), $collection->getResources());
        }

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

    /**
     * @EXT\Route(
     *     "/{workspace}/workspace",
     *     name="apiv2_resource_workspace_removed_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function listRemovedAction(Workspace $workspace, Request $request)
    {
        $filters = [
            'workspace' => $workspace->getUuid(),
            'active' => false,
        ];

        return new JsonResponse(
            $this->finder->search(ResourceNode::class,
            array_merge($request->query->all(), ['hiddenFilters' => $filters]))
        );
    }
}
