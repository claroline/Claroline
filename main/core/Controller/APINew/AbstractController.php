<?php

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\CoreBundle\API\Crud;
use Claroline\CoreBundle\API\FinderProvider;
use Claroline\CoreBundle\API\SerializerProvider;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends ContainerAware
{
    /** @var FinderProvider */
    protected $finder;

    /** @var SerializerProvider */
    protected $serializer;

    /** @var Crud */
    protected $crud;

    /** @var ObjectManager */
    protected $om;

    /** @var ContainerInterface */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->finder = $container->get('claroline.api.finder');
        $this->serializer = $container->get('claroline.api.serializer');
        $this->crud = $container->get('claroline.api.crud');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function getAction(Request $request, $class, $env)
    {
        $object = $this->om->getRepository($class)->findOneBy($request->query->get('filters'));

        return $object ?
            new JsonResponse($this->serializer->serialize($object)) :
            new JsonResponse('', 404);
    }

    public function listAction(Request $request, $class, $env)
    {
        return new JsonResponse($this->finder->search($class, $request->query->all()));
    }

    public function createAction(Request $request, $class, $env)
    {
        try {
            $object = $this->crud->create($class, $this->decodeRequest($request));

            return new JsonResponse(
              $this->serializer->serialize($object),
              201
            );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }

    public function updateAction($uuid, Request $request, $class, $env)
    {
        try {
            $object = $this->crud->update($class, $this->decodeRequest($request));

            return new JsonResponse(
                $this->serializer->serialize($object)
            );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }

    public function deleteBulkAction(Request $request, $class, $env)
    {
        try {
            $this->crud->deleteBulk($class, $this->decodeIdsString($request, $class));

            return new JsonResponse(null, 204);
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }

    protected function handleException(\Exception $e, $env)
    {
        if ($env === 'prod') {
            return new JsonResponse($e->getMessage(), 422);
        }

        throw $e;
    }

    protected function decodeRequest(Request $request)
    {
        return json_decode($request->getContent());
    }

    protected function decodeIdsString(Request $request, $class)
    {
        $ids = $request->query->get('ids');
        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }

    //@todo: not as lazy implementation
    protected function find($class, $id)
    {
        return !is_numeric($id) && property_exists($class, 'uuid') ?
            $this->om->getRepository($class)->findOneByUuid($id) :
            $this->om->getRepository($class)->findOneById($id);
    }
}
