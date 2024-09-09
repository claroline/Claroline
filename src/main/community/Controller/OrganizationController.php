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
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasUsersTrait;
use Claroline\CoreBundle\Controller\Model\HasWorkspacesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/organization", name="apiv2_organization_")
 */
class OrganizationController extends AbstractCrudController
{
    use HasGroupsTrait;
    use HasUsersTrait;
    use HasWorkspacesTrait;
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

    /**
     * @Route("/list/recursive", name="list_recursive")
     */
    public function recursiveListAction(Request $request): JsonResponse
    {
        $query = $request->query->all();

        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();
        // only get the root organization to build the tree
        $query['hiddenFilters']['parent'] = null;

        return new JsonResponse(
            $this->crud->list(Organization::class, $query, [Options::IS_RECURSIVE])
        );
    }

    /**
     * @Route("/{id}/managers", name="list_managers", methods={"GET"})
     *
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     */
    public function listManagersAction(Organization $organization): JsonResponse
    {
        $this->checkPermission('OPEN', $organization, [], true);

        return new JsonResponse(
            $this->crud->list(User::class, [
                'hiddenFilters' => ['organizationManager' => $organization->getUuid()],
            ])
        );
    }

    /**
     * Adds managers to the collection.
     *
     * @Route("/{id}/manager", name="add_managers", methods={"PATCH"})
     *
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     */
    public function addManagersAction(Organization $organization, Request $request): JsonResponse
    {
        $users = $this->decodeIdsString($request, User::class);
        $this->crud->patch($organization, 'manager', Crud::COLLECTION_ADD, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }

    /**
     * Removes managers from the collection.
     *
     * @Route("/{id}/manager", name="remove_managers", methods={"DELETE"})
     *
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     */
    public function removeManagersAction(Organization $organization, Request $request): JsonResponse
    {
        $users = $this->decodeIdsString($request, User::class);
        $this->crud->patch($organization, 'manager', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                // show user organizations
                return [
                    'user' => $user->getUuid(),
                ];
            }

            // only show public organizations
            return [
                'public' => true,
            ];
        }

        // show all to admins
        return [];
    }
}
