<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\CloseResourceEvent;
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
    private $dispatcher;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var RightsManager */
    private $rightsManager;

    /** @var ResourceActionManager */
    private $actionManager;

    /** @var LogConnectManager */
    private $logConnectManager;

    /** @var TokenStorageInterface */
    private $token;

    public function __construct(
        StrictDispatcher $dispatcher,
        ResourceActionManager $actionManager,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        LogConnectManager $logConnectManager,
        TokenStorageInterface $token
    ) {
        $this->dispatcher = $dispatcher;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->actionManager = $actionManager;
        $this->logConnectManager = $logConnectManager;
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

        $roles = $this->token->getToken()->getRoleNames();
        if (!in_array('ROLE_ADMIN', $roles) || empty($options['hiddenFilters']['parent'])) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options, $this->getOptions()['list'])
        );
    }

    /**
     * Get the list of rights for a resource node.
     * This may be directly managed by the standard action system (rights edition already is) instead.
     *
     * @Route("/{id}/rights", name="apiv2_resource_get_rights")
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
     */
    public function getRightsAction(ResourceNode $resourceNode): JsonResponse
    {
        // only give access to users which have the right to edit the resource rights
        $rightsAction = $this->actionManager->get($resourceNode, 'rights');

        $collection = new ResourceCollection([$resourceNode]);
        if (!$this->actionManager->hasPermission($rightsAction, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        return new JsonResponse(
            array_values($this->rightsManager->getRights($resourceNode))
        );
    }

    /**
     * @Route("/{workspace}/removed", name="apiv2_resource_workspace_removed_list")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
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
     * @EXT\ParamConverter("resourceNode", class="Claroline\CoreBundle\Entity\Resource\ResourceNode", options={"mapping": {"slug": "slug"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function closeAction(ResourceNode $resourceNode, Request $request, User $user = null): JsonResponse
    {
        $this->dispatcher->dispatch(
            ResourceEvents::CLOSE,
            CloseResourceEvent::class,
            [$this->resourceManager->getResourceFromNode($resourceNode)]
        );

        // todo : listen to close event instead
        if ($user) {
            $data = $this->decodeRequest($request);

            if (isset($data['embedded'])) {
                $this->logConnectManager->computeResourceDuration($user, $resourceNode, $data['embedded']);
            }
        }

        return new JsonResponse(null, 204);
    }

    public function getOptions()
    {
        return [
            'list' => [Options::NO_RIGHTS, Options::SERIALIZE_LIST],
            'get' => [Options::NO_RIGHTS],
            'update' => [Options::NO_RIGHTS],
            'find' => [Options::NO_RIGHTS],
        ];
    }
}
