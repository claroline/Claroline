<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages a workspaces collection on an entity.
 */
trait HasWorkspacesTrait
{
    /**
     * List workspaces of the collection.
     *
     * @EXT\Route("/{id}/workspace")
     * @EXT\Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listWorkspacesAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Workspace\Workspace', array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * Adds workspaces to the collection.
     *
     * @EXT\Route("/{id}/workspace")
     * @EXT\Method("PATCH")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addWorkspacesAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $workspaces = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace');
        $this->crud->patch($object, 'user', Crud::COLLECTION_ADD, $workspaces);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes workspaces from the collection.
     *
     * @EXT\Route("/{id}/workspace")
     * @EXT\Method("DELETE")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeWorkspacesAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $workspaces = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace');
        $this->crud->patch($object, 'user', Crud::COLLECTION_REMOVE, $workspaces);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
