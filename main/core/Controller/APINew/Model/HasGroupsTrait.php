<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\CoreBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait HasGroupsTrait
{
    /**
     * @Route("{uuid}/group")
     * @Method("PATCH")
     */
    public function addGroupsAction($uuid, $class, Request $request, $env)
    {
        try {
            $object = $this->find($class, $uuid);
            $groups = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Group');
            $this->crud->patch($object, 'group', Crud::COLLECTION_ADD, $groups);

            return new JsonResponse(
                $this->serializer->serialize($object)
            );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }

    /**
     * @Route("{uuid}/group")
     * @Method("DELETE")
     */
    public function removeGroupsAction($uuid, $class, Request $request, $env)
    {
        try {
            $object = $this->find($class, $uuid);
            $groups = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Group');
            $this->crud->patch($object, 'group', Crud::COLLECTION_REMOVE, $groups);

            return new JsonResponse(
              $this->serializer->serialize($object)
          );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }
}
