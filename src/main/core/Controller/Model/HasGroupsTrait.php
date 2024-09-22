<?php

namespace Claroline\CoreBundle\Controller\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages a groups collection on an entity.
 */
trait HasGroupsTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    abstract public static function getClass(): string;

    abstract public static function getName(): string;

    /**
     * List groups of the collection.
     *
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\Group.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\Group"}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    #[Route(path: '/{id}/group', name: 'list_groups', methods: ['GET'], priority: 1)]
    public function listGroupsAction(string $id, User $user, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $this->crud->get(static::getClass(), $id);

        $hiddenFilters = [
            // filter the list by the parent
            static::getName() => [$id],
        ];

        if (!$this->checkPermission('ROLE_ADMIN')) {
            // only list groups for the current user organizations
            $hiddenFilters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations());
        }

        return new JsonResponse(
            $this->crud->list(Group::class, array_merge($request->query->all(), [
                'hiddenFilters' => $hiddenFilters,
            ]))
        );
    }

    /**
     * Adds groups to the collection.
     *
     * @ApiDoc(
     *     description="Add objects of class Claroline\CoreBundle\Entity\Group.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The groups id or uuid."}
     *     }
     * )
     */
    #[Route(path: '/{id}/group', name: 'add_groups', methods: ['PATCH'], priority: 1)]
    public function addGroupsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);

        $groups = $this->decodeIdsString($request, Group::class);
        $this->crud->patch($object, 'group', Crud::COLLECTION_ADD, $groups);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes groups from the collection.
     *
     * @ApiDoc(
     *     description="Removes objects of class Claroline\CoreBundle\Entity\Group.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The groups id or uuid."}
     *     }
     * )
     */
    #[Route(path: '/{id}/group', name: 'remove_groups', methods: ['DELETE'], priority: 1)]
    public function removeGroupsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);

        $groups = $this->decodeIdsString($request, Group::class);
        $this->crud->patch($object, 'group', Crud::COLLECTION_REMOVE, $groups);

        return new JsonResponse($this->serializer->serialize($object));
    }
}
