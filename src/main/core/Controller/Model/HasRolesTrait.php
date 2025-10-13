<?php

namespace Claroline\CoreBundle\Controller\Model;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages a roles collection on an entity.
 */
trait HasRolesTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    abstract public static function getClass(): string;

    abstract public static function getName(): string;

    #[Route(path: '/{id}/role', name: 'list_roles', methods: ['GET'], priority: 1)]
    public function listRolesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $this->crud->get(static::getClass(), $id);

        return new JsonResponse(
            $this->crud->list(Role::class, array_merge($request->query->all(), [
                'hiddenFilters' => [
                    static::getName() => [$id],
                ],
            ]))
        );
    }

    #[Route(path: '/{id}/role', name: 'add_roles', methods: ['PATCH'], priority: 1)]
    public function addRolesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);
        $ids = $this->decodeRequest($request);
        $roles = $this->om->getRepository(Role::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($object, 'role', Crud::COLLECTION_ADD, $roles);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    #[Route(path: '/{id}/role', name: 'remove_roles', methods: ['DELETE'], priority: 1)]
    public function removeRolesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);
        $ids = $this->decodeRequest($request);
        $roles = $this->om->getRepository(Role::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($object, 'role', Crud::COLLECTION_REMOVE, $roles);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
