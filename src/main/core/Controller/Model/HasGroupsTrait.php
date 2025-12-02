<?php

namespace Claroline\CoreBundle\Controller\Model;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CoreBundle\Entity\Group;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages a group collection on an entity.
 */
trait HasGroupsTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    abstract public static function getClass(): string;

    abstract public static function getName(): string;

    #[Route(path: '/{id}/group', name: 'list_groups', methods: ['GET'], priority: 1)]
    public function listGroupsAction(
        string $id,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        // no need to secure entrypoint, the CRUD will do it for us.
        $this->crud->get(static::getClass(), $id);

        // filter the list by the parent
        $finderRequest->addFilter(static::getName(), $id);

        $groups = $this->crud->search(Group::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $groups->toResponse();
    }

    #[Route(path: '/{id}/group', name: 'add_groups', methods: ['PATCH'], priority: 1)]
    public function addGroupsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);
        $ids = $this->decodeRequest($request);
        $groups = $this->om->getRepository(Group::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($object, 'group', Crud::COLLECTION_ADD, $groups);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    #[Route(path: '/{id}/group', name: 'remove_groups', methods: ['DELETE'], priority: 1)]
    public function removeGroupsAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);
        $ids = $this->decodeRequest($request);
        $groups = $this->om->getRepository(Group::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($object, 'group', Crud::COLLECTION_REMOVE, $groups);

        return new JsonResponse($this->serializer->serialize($object));
    }
}
