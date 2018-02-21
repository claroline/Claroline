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

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasParentTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasWorkspacesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Organization\Organization")
 * @Route("/organization")
 */
class OrganizationController extends AbstractCrudController
{
    public function getName()
    {
        return 'organization';
    }

    use HasParentTrait;
    use HasUsersTrait;
    use HasGroupsTrait;
    use HasWorkspacesTrait;

    /**
     * @Route("/list/recursive", name="apiv2_organization_list_recursive")
     */
    public function recursiveListAction()
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Organization\Organization',
            ['hiddenFilters' => ['parent' => null]],
            [Options::IS_RECURSIVE]
        ));
    }

    /**
     * @Route("/{id}/managers", name="apiv2_organization_list_managers")
     * @Method("GET")
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     *
     * @param Organization $organization
     *
     * @return JsonResponse
     */
    public function listManagersAction(Organization $organization)
    {
        return new JsonResponse($this->finder->search(
             'Claroline\CoreBundle\Entity\User',
             ['hiddenFilters' => ['organizationManager' => $organization->getUuid()]]
         ));
    }

    /**
     * Adds managers to the collection.
     *
     * @Route("/{id}/manager", name="apiv2_organization_add_managers")
     * @Method("PATCH")
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     *
     * @param Organization $organization
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function addManagersAction(Organization $organization, Request $request)
    {
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($organization, 'administrator', Crud::COLLECTION_ADD, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }

    /**
     * Removes managers from the collection.
     *
     * @Route("/{id}/manager", name="apiv2_organization_remove_managers")
     * @Method("DELETE")
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     *
     * @param Organization $organization
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function removeManagersAction(Organization $organization, Request $request)
    {
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($organization, 'administrator', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse($this->serializer->serialize($organization));
    }
}
