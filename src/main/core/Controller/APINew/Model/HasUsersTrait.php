<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages a users collection on an entity.
 */
trait HasUsersTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    /**
     * List users of the collection.
     *
     * @Route("/{id}/user", methods={"GET"})
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\User.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\User"}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function listUsersAction(string $id, string $class, User $user, Request $request): JsonResponse
    {
        $object = $this->crud->get($class, $id);
        $this->checkPermission('OPEN', $object, [], true);

        $hiddenFilters = [
            // filter the list by the parent
            $this->getName() => [$id],
        ];

        if (!$this->checkPermission('ROLE_ADMIN')) {
            // only list users for the current user organizations
            $hiddenFilters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations());
        }

        return new JsonResponse(
            $this->crud->list(User::class, array_merge($request->query->all(), [
                'hiddenFilters' => $hiddenFilters,
            ]))
        );
    }

    /**
     * Adds users to the collection.
     *
     * @Route("/{id}/user", methods={"PATCH"})
     * @ApiDoc(
     *     description="Add objects of class Claroline\CoreBundle\Entity\User.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The user id or uuid."}
     *     }
     * )
     */
    public function addUsersAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $users = $this->decodeIdsString($request, User::class);
        $this->crud->patch($object, 'user', Crud::COLLECTION_ADD, $users);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes users from the collection.
     *
     * @Route("/{id}/user", methods={"DELETE"})
     * @ApiDoc(
     *     description="Removes objects of class Claroline\CoreBundle\Entity\User.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The user id or uuid."}
     *     }
     * )
     */
    public function removeUsersAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $users = $this->decodeIdsString($request, User::class);
        $this->crud->patch($object, 'user', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
