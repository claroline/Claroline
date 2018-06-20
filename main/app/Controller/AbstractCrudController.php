<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Routing\Documentator;
use Claroline\AppBundle\API\Routing\Finder;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractCrudController extends AbstractApiController
{
    /** @var FinderProvider */
    protected $finder;

    /** @var Finder */
    protected $routerFinder;

    /** @var Documentator */
    protected $routerDocumentator;

    /** @var SerializerProvider */
    protected $serializer;

    /** @var Crud */
    protected $crud;

    /** @var ObjectManager */
    protected $om;

    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    protected $options;

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    abstract public function getName();

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->finder = $container->get('claroline.api.finder');
        $this->serializer = $container->get('claroline.api.serializer');
        $this->crud = $container->get('claroline.api.crud');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->routerFinder = $container->get('claroline.api.routing.finder');
        $this->routerDocumentator = $container->get('claroline.api.routing.documentator');
        $this->options = $this->mergeOptions();
    }

    /**
     * @ApiDoc(
     *     description="Find a single object of class $class.",
     *     queryString={"$finder"}
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function findAction(Request $request, $class)
    {
        $query = $request->query->all();
        $data = $this->finder->fetch($class, $query['filters'], [], 0, 2);

        switch (count($data)) {
            case 0:
                return $this->sendResponse('No object found', 404);
                break;
            case 1:
                return $this->sendResponse(
                    $this->serializer->serialize($data[0], $this->options['get'])
                );
                break;
            default:
                return $this->sendResponse('Multiple results, use "list" instead', 400);
        }
    }

    /**
     * @ApiDoc(
     *     description="Return the schema of class $class."
     * )
     *
     * @param string $class
     *
     * @return JsonResponse
     */
    public function schemaAction($class)
    {
        return $this->sendResponse($this->serializer->getSchema($class));
    }

    /**
     * @ApiDoc(
     *     description="Finds an object class $class.",
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The object id or uuid"
     *          }
     *     }
     * )
     *
     * @param string|int $id
     * @param string     $class
     * @param string     $env
     *
     * @return JsonResponse
     */
    public function getAction($id, $class)
    {
        $object = $this->find($class, $id);

        return $object ?
            $this->sendResponse(
                $this->serializer->serialize($object, $this->options['get'])
            ) :
            $this->sendResponse("No object found for id {$id} of class {$class}", 404);
    }

    /**
     * @ApiDoc(
     *     description="Check if an object exists (it'll eventually fire a doctrine findBy method)",
     *     parameters={
     *         {"name": "field", "type": "string", "description": "The queried field."},
     *         {"name": "value", "type": "mixed", "description": "The value of the field"}
     *     }
     * )
     *
     * @return JsonResponse
     */
    public function existAction($class, $field, $value)
    {
        $objects = $this->om->getRepository($class)->findBy([$field => $value]);

        return $this->sendResponse(count($objects) > 0);
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, $class)
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, $this->getDefaultHiddenFilters());

        return $this->sendResponse($this->finder->search(
            $class,
            $query,
            $this->options['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="Create an object class $class.",
     *     body={
     *         "schema":"$schema"
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $class)
    {
        $object = $this->crud->create(
            $class,
            $this->decodeRequest($request),
            $this->options['create']
        );

        if (is_array($object)) {
            return $this->sendResponse($object, 400);
        }

        return $this->sendResponse(
            $this->serializer->serialize($object, $this->options['get']),
            201
        );
    }

    /**
     * @ApiDoc(
     *     description="Update an object class $class.",
     *     body={
     *         "schema":"$schema"
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The object id or uuid"
     *          }
     *     }
     * )
     *
     * @param string|int $id
     * @param Request    $request
     * @param string     $class
     *
     * @return JsonResponse
     */
    public function updateAction($id, Request $request, $class)
    {
        $data = $this->decodeRequest($request);

        if (!isset($data['id'])) {
            $data['id'] = $id;
        }

        $object = $this->crud->update(
            $class,
            $data,
            $this->options['update']
        );

        if (is_array($object)) {
            return $this->sendResponse($object, 400);
        }

        return $this->sendResponse(
            $this->serializer->serialize($object, $this->options['get'])
        );
    }

    /**
     * @ApiDoc(
     *     description="Remove an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, $class)
    {
        $this->crud->deleteBulk(
            $this->decodeIdsString($request, $class),
            $this->options['deleteBulk']
        );

        return $this->sendResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Copy an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function copyBulkAction(Request $request, $class)
    {
        $serializer = $this->serializer;
        $options = $this->options;

        $copies = $this->crud->copyBulk(
            $class,
            $this->decodeIdsString($request, $class),
            $this->options['copyBulk']
        );

        return $this->sendResponse(array_map(function ($copy) use ($serializer, $options) {
            return $serializer->serialize($copy, $options['get']);
        }, $copies), 200);
    }

    /**
     * @ApiDoc(
     *     description="Display the current informations",
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function docAction(Request $request, $class)
    {
        return new JsonResponse($this->routerDocumentator->documentClass($class));
    }

    /**
     * @param Request $request
     * @param string  $class
     */
    protected function decodeIdsString(Request $request, $class)
    {
        $ids = $request->query->get('ids');
        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }

    /**
     * @param string     $class
     * @param string|int $id
     */
    protected function find($class, $id)
    {
        return $this->om->getRepository($class)->findOneBy(
            !is_numeric($id) && property_exists($class, 'uuid') ?
                ['uuid' => $id] :
                ['id' => $id]
        );
    }

    private function sendResponse($data, $code = 200)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $debug = $request->query->get('debug');

        if (!$debug) {
            return new JsonResponse($data, $code);
        }

        //this is for debug purpose
        return new Response('<body>'.json_encode($data).'</body>', $code);
    }

    /**
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            'list' => [],
            'get' => [],
            'create' => [],
            'update' => [],
            'deleteBulk' => [],
            'copyBulk' => [],
            'exist' => [],
            'schema' => [],
            'find' => [],
            'doc' => [],
        ];
    }

    /**
     * @return array
     */
    private function getDefaultRequirements()
    {
        return [
          'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|\/)).*'],
          'update' => ['id' => '^(?!.*(schema|parameters|find|doc|\/)).*'],
          'exist' => [],
        ];
    }

    /**
     * @return array
     */
    protected function getRequirements()
    {
        return [];
    }

    /**
     * @return array
     */
    public function mergeRequirements()
    {
        return array_merge($this->getDefaultRequirements(), $this->getRequirements());
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    /**
     * @return array
     */
    private function mergeOptions()
    {
        return array_merge_recursive($this->getDefaultOptions(), $this->getOptions());
    }

    /**
     * @return array
     */
    public function getDefaultHiddenFilters()
    {
        return [];
    }

    /**
     * Should replace ApiMeta.
     */
    public function getClass()
    {
        return null;
    }

    /**
     * Should replace ApiMeta.
     */
    public function getIgnore()
    {
        return [];
    }
}
