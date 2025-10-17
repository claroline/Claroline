<?php

namespace Claroline\CoreBundle\Controller\Model;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Manages a collection of organizations on an entity.
 */
trait HasOrganizationsTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    abstract public static function getClass(): string;

    abstract public static function getName(): string;

    /**
     * List all the organizations of an entity.
     */
    #[Route(path: '/{id}/organization', name: 'list_organizations', methods: ['GET'], priority: 1)]
    public function listOrganizationsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $this->crud->get(static::getClass(), $id);

        return new JsonResponse(
            $this->crud->list(Organization::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [static::getName() => [$id]]]
            ))
        );
    }

    /**
     * Add an organization to the entity.
     */
    #[Route(path: '/{id}/organization', name: 'add_organizations', methods: ['PATCH'], priority: 1)]
    public function addOrganizationsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);
        $ids = $this->decodeRequest($request);
        $organizations = $this->om->getRepository(Organization::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($object, 'organization', Crud::COLLECTION_ADD, $organizations);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Remove an organization from the entity.
     */
    #[Route(path: '/{id}/organization', name: 'remove_organizations', methods: ['DELETE'], priority: 1)]
    public function removeOrganizationsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);
        $ids = $this->decodeRequest($request);
        $organizations = $this->om->getRepository(Organization::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($object, 'organization', Crud::COLLECTION_REMOVE, $organizations);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Add a list of entities to the current organization of the authenticated user.
     */
    #[Route(path: '/current-organization', name: 'add_current_organization', methods: ['PUT'], priority: 1)]
    public function addToCurrentOrganizationAction(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        // retrieve the list of entities to add to the current organization
        $entityIds = $this->decodeRequest($request);
        $entities = $this->om->getRepository(static::getClass())->findBy(['uuid' => $entityIds]);
        foreach ($entities as $entity) {
            $this->crud->patch($entity, 'organization', Crud::COLLECTION_ADD, [$user->getMainOrganization()]);
        }

        return new JsonResponse(null, 204);
    }
}
