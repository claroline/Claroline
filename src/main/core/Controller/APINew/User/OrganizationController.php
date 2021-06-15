<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\User;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasParentTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasWorkspacesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/organization")
 */
class OrganizationController extends AbstractCrudController
{
    use HasGroupsTrait;
    use HasParentTrait;
    use HasUsersTrait;
    use HasWorkspacesTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function getName()
    {
        return 'organization';
    }

    public function getClass()
    {
        return Organization::class;
    }

    /**
     * @Route("/list/recursive", name="apiv2_organization_list_recursive")
     */
    public function recursiveListAction(): JsonResponse
    {
        return new JsonResponse(
            $this->finder->search(Organization::class, [
                // only get the root organization to build the tree
                'hiddenFilters' => ['parent' => null],
            ], [Options::IS_RECURSIVE])
        );
    }

    /**
     * @Route("/{id}/managers", name="apiv2_organization_list_managers", methods={"GET"})
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     */
    public function listManagersAction(Organization $organization): JsonResponse
    {
        return new JsonResponse($this->finder->search(
             'Claroline\CoreBundle\Entity\User',
             ['hiddenFilters' => ['organizationManager' => $organization->getUuid()]]
         ));
    }

    /**
     * Adds managers to the collection.
     *
     * @Route("/{id}/manager", name="apiv2_organization_add_managers", methods={"PATCH"})
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     */
    public function addManagersAction(Organization $organization, Request $request): JsonResponse
    {
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($organization, 'administrator', Crud::COLLECTION_ADD, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }

    /**
     * Removes managers from the collection.
     *
     * @Route("/{id}/manager", name="apiv2_organization_remove_managers", methods={"DELETE"})
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     */
    public function removeManagersAction(Organization $organization, Request $request): JsonResponse
    {
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($organization, 'administrator', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }
}
