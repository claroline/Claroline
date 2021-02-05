<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/resource")
 */
class ResourceNodeController extends AbstractCrudController
{
    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var RightsManager */
    private $rightsManager;

    /** @var ResourceActionManager */
    private $actionManager;

    /** @var LogConnectManager */
    private $logConnectManager;

    /** @var ParametersSerializer */
    private $parametersSerializer;

    /** @var TokenStorageInterface */
    private $token;

    public function __construct(
        StrictDispatcher $eventDispatcher,
        ResourceActionManager $actionManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        LogConnectManager $logConnectManager,
        ParametersSerializer $parametersSerializer,
        TokenStorageInterface $token
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->actionManager = $actionManager;
        $this->logConnectManager = $logConnectManager;
        $this->parametersSerializer = $parametersSerializer;
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'resource_node';
    }

    /**
     * @Route("/{parent}/{all}", name="apiv2_resource_list", defaults={"parent"=null}, requirements={"all": "all"})
     *
     * @param string $parent
     * @param string $all
     * @param string $class
     */
    public function listAction(Request $request, $parent, $all = null, $class = ResourceNode::class): JsonResponse
    {
        $options = $request->query->all();

        if ($parent) {
            // grab directory content
            /** @var ResourceNode $parentNode */
            $parentNode = $this->om->getRepository(ResourceNode::class)->findOneByUuidOrSlug($parent);

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

        $roles = $this->token->getToken()->getRoleNames();

        if (!in_array('ROLE_ADMIN', $roles) || empty($options['hiddenFilters']['parent'])) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)
        );
    }

    /**
     * @Route("/{parent}/files", name="apiv2_resource_files_create")
     * @EXT\ParamConverter("parent", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"parent": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function resourceFilesCreateAction(ResourceNode $parent, User $user, Request $request): JsonResponse
    {
        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);

        if (isset($parameters['restrictions']['storage']) &&
            isset($parameters['restrictions']['max_storage_reached']) &&
            $parameters['restrictions']['storage'] &&
            $parameters['restrictions']['max_storage_reached']
        ) {
            throw new AccessDeniedException();
        }

        $attributes = [];
        $attributes['type'] = 'file';
        $collection = new ResourceCollection([$parent]);
        $collection->setAttributes($attributes);

        $add = $this->actionManager->get($parent, 'add');

        if (!$this->actionManager->hasPermission($add, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay(), $collection->getResources());
        }

        $filesData = $request->files->all();
        $files = isset($filesData['files']) ? $filesData['files'] : [];
        $handler = $request->get('handler');
        $publicFiles = [];
        $resources = [];

        foreach ($files as $file) {
            $publicFile = $this->crud->create(
                'Claroline\CoreBundle\Entity\File\PublicFile',
                [],
                ['file' => $file]
            );
            $this->eventDispatcher->dispatch(strtolower('upload_file_'.$handler), 'File\UploadFile', [$publicFile]);
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

            // TODO : use crud instead
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
     * @Route("/{workspace}/removed", name="apiv2_resource_workspace_removed_list")
     * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listRemovedAction(Workspace $workspace, Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->finder->search(ResourceNode::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
                'active' => false,
            ]]))
        );
    }

    /**
     * @Route("/{slug}/close", name="claro_resource_close", methods={"PUT"})
     * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"slug": "slug"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function closeAction(ResourceNode $resourceNode, Request $request, User $user = null): JsonResponse
    {
        if ($user) {
            $data = $this->decodeRequest($request);

            if (isset($data['embedded'])) {
                $this->logConnectManager->computeResourceDuration($user, $resourceNode, $data['embedded']);
            }
        }

        return new JsonResponse(null, 204);
    }
}
