<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/organization', name: 'apiv2_organization_')]
class OrganizationController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'organization';
    }

    public static function getClass(): string
    {
        return Organization::class;
    }

    #[Route(path: '/{id}/managers', name: 'list_managers', methods: ['GET'])]
    public function listManagersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Organization $organization
    ): JsonResponse {
        $this->checkPermission('OPEN', $organization, [], true);

        return new JsonResponse(
            $this->crud->list(User::class, [
                'hiddenFilters' => ['organizationManager' => $organization->getUuid()],
            ])
        );
    }

    /**
     * Adds managers to the collection.
     */
    #[Route(path: '/{id}/manager', name: 'add_managers', methods: ['PATCH'])]
    public function addManagersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Organization $organization,
        Request $request
    ): JsonResponse {
        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($organization, 'manager', Crud::COLLECTION_ADD, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }

    /**
     * Removes managers from the collection.
     */
    #[Route(path: '/{id}/manager', name: 'remove_managers', methods: ['DELETE'])]
    public function removeManagersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Organization $organization,
        Request $request
    ): JsonResponse {
        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

        $this->crud->patch($organization, 'manager', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }

    public static function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            'delete' => [Options::FORCE_FLUSH],
        ]);
    }
}
