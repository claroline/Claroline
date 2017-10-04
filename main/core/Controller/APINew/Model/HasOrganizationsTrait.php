<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\CoreBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait HasOrganizationsTrait
{
    /**
     * @Route("{uuid}/organization")
     * @Method("PATCH")
     */
    public function addOrganizationsAction($uuid, $class, Request $request, $env)
    {
        try {
            $object = $this->find($class, $uuid);
            $organizations = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Organization\Organization');
            $this->crud->patch($object, 'organizations', Crud::ADD_ARRAY_ELEMENT, $organizations);

            return new JsonResponse(
              $this->serializer->serialize($object)
          );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }

    /**
     * @Route("{uuid}/organization")
     * @Method("DELETE")
     */
    public function removeOrganizationsAction($uuid, $class, Request $request, $env)
    {
        try {
            $object = $this->find($class, $uuid);
            $organizations = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Organization\Organization');
            $this->crud->patch($object, 'organizations', Crud::REMOVE_ARRAY_ELEMENT, $organizations);

            return new JsonResponse(
            $this->serializer->serialize($object)
        );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }
}
