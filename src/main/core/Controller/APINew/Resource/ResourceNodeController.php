<?php

namespace Claroline\CoreBundle\Controller\APINew\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
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
    public function __construct(
        private readonly ResourceActionManager $actionManager,
        private readonly RightsManager $rightsManager,
        private readonly TokenStorageInterface $token
    ) {
    }

    public function getName(): string
    {
        return 'resource_node';
    }

    public function getClass(): string
    {
        return ResourceNode::class;
    }

    public function getIgnore(): array
    {
        return ['list'];
    }

    /**
     * @Route("/{parent}/{all}", name="apiv2_resource_list", defaults={"parent"=null}, requirements={"all": "all"})
     *
     * @param string $parent
     * @param string $all
     */
    public function listChildrenAction(Request $request, $parent, $all = null): JsonResponse
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
            $this->crud->list(ResourceNode::class, $options, $this->getOptions()['list'])
        );
    }

    /**
     * Get the list of rights for a resource node.
     * This may be directly managed by the standard action system (rights edition already is) instead.
     *
     * @Route("/{id}/rights", name="apiv2_resource_get_rights")
     *
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
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listRemovedAction(Workspace $workspace, Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->crud->list(ResourceNode::class,
                array_merge($request->query->all(), ['hiddenFilters' => [
                    'workspace' => $workspace->getUuid(),
                    'active' => false,
                ]])
            )
        );
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'list' => [Options::NO_RIGHTS, Options::SERIALIZE_LIST],
            'get' => [Options::NO_RIGHTS],
            'update' => [Options::NO_RIGHTS],
        ]);
    }
}
