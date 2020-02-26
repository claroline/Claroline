<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Routing\Documentator;
use Claroline\AppBundle\Routing\Finder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @var array
     *
     * @deprecated should not be stored
     */
    protected $options = [];

    /** @var ContainerInterface */
    protected $container;

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * @param ContainerInterface $container
     *
     * @deprecated use setter injection instead
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->finder = $container->get('Claroline\AppBundle\API\FinderProvider');
        $this->serializer = $container->get('Claroline\AppBundle\API\SerializerProvider');
        $this->crud = $container->get('Claroline\AppBundle\API\Crud');
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $this->routerFinder = $container->get('Claroline\AppBundle\Routing\Finder');
        $this->routerDocumentator = $container->get('Claroline\AppBundle\Routing\Documentator');
    }

    //these are the injectors you should use
    public function setFinder(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function setSerializer(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setCrud(Crud $crud)
    {
        $this->crud = $crud;
        $this->options = $this->getOptions(); // TODO : remove me.
    }

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function setRouterFinder(Finder $routerFinder)
    {
        $this->routerFinder = $routerFinder;
    }

    public function setRouterDocumentator(Documentator $routerDocumentator)
    {
        $this->routerDocumentator = $routerDocumentator;
    }

    //end

    /**
     * @ApiDoc(
     *     description="Find a single object of class $class.",
     *     queryString={"$finder"},
     *     response={"$object"}
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

        $options = $this->options['get'];
        if (isset($query['options'])) {
            $options = $query['options'];
        }

        switch (count($data)) {
            case 0:
                return new JsonResponse('No object found', 404);
                break;
            case 1:
                return new JsonResponse(
                    $this->serializer->serialize($data[0], $options)
                );
                break;
            default:
                return new JsonResponse('Multiple results, use "list" instead', 400);
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
        return new JsonResponse($this->serializer->getSchema($class));
    }

    /**
     * @ApiDoc(
     *     description="Finds an object class $class.",
     *     parameters={
     *          {"name": "id", "type": {"string", "integer"}, "description": "The object id or uuid"}
     *     },
     *     response={"$object"}
     * )
     *
     * @param Request    $request
     * @param string|int $id
     * @param string     $class
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, $class)
    {
        $query = $request->query->all();
        $object = $this->find($class, $id);

        $options = $this->options['get'];
        if (isset($query['options'])) {
            $options = $query['options'];
        }

        if ($object) {
            return new JsonResponse(
                $this->serializer->serialize($object, $options ?? [])
            );
        }

        return new JsonResponse("No object found for id {$id} of class {$class}", 404);
    }

    /**
     * @ApiDoc(
     *     description="Check if an object exists (it'll eventually fire a doctrine findBy method)",
     *     parameters={
     *         {"name": "field", "type": "string", "description": "The queried field."},
     *         {"name": "value", "type": "string", "description": "The value of the field"}
     *     }
     * )
     *
     * @param string $class
     * @param string $field
     * @param mixed  $value
     *
     * @return JsonResponse
     */
    public function existAction($class, $field, $value)
    {
        $objects = $this->om->getRepository($class)->findBy([$field => $value]);

        if (count($objects) > 0) {
            return new JsonResponse(true);
        }

        return new JsonResponse(false, 204);
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
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
        $options = $this->options['list'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();

        return new JsonResponse(
            $this->finder->search($class, $query, $options ?? [])
        );
    }

    /**
     * @ApiDoc(
     *     description="Export the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."},
     *         {"name": "columns", "type": "array", "description": "The list of columns to export."}
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return BinaryFileResponse
     */
    public function csvAction(Request $request, $class)
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, $this->getDefaultHiddenFilters());

        $data = $this->finder->search(
            $class,
            $query,
            $options ?? []
        )['data'];

        $titles = [];
        $formatted = [];
        if (!empty($data[0])) {
            $firstRow = $data[0];
            //get the title list
            $titles = !empty($query['columns']) ? $query['columns'] : ArrayUtils::getPropertiesName($firstRow);

            foreach ($data as $el) {
                $formattedData = [];
                foreach ($titles as $title) {
                    $formattedData[$title] = ArrayUtils::has($el, $title) ?
                        ArrayUtils::get($el, $title) : null;
                    $formattedData[$title] = !is_array($formattedData[$title]) ? $formattedData[$title] : json_encode($formattedData[$title]);
                }
                $formatted[] = $formattedData;
            }
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'CSVCLARO').'.csv';
        $fp = fopen($tmpFile, 'w');

        fputcsv($fp, $titles, ';');

        foreach ($formatted as $item) {
            fputcsv($fp, $item, ';');
        }

        fclose($fp);

        return new BinaryFileResponse($tmpFile, 200, [
            'Content-Disposition' => "attachment; filename={$this->getName()}.csv",
        ]);
    }

    /**
     * @ApiDoc(
     *     description="Create an object class $class.",
     *     body={
     *         "schema":"$schema"
     *     },
     *     response={"$object"}
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $class)
    {
        $query = $request->query->all();

        $options = $this->options['create'];
        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $object = $this->crud->create($class, $this->decodeRequest($request), $options ?? []);

        if (is_array($object)) {
            return new JsonResponse($object, 422);
        }

        return new JsonResponse(
            $this->serializer->serialize($object, $options),
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
     *          {"name": "id", "type": {"string", "integer"}, "description": "The object id or uuid"}
     *     },
     *     response={"$object"}
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
        $query = $request->query->all();

        $data = $this->decodeRequest($request);
        if (!isset($data['id'])) {
            $data['id'] = $id;
        }

        $options = $this->options['update'];
        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $object = $this->crud->update($class, $data, $options ?? []);

        if (is_array($object)) {
            return new JsonResponse($object, 422);
        }

        //just in case so we really returns the proper object
        $this->om->refresh($object);

        return new JsonResponse(
            $this->serializer->serialize($object, $options)
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
        $query = $request->query->all();
        $options = $this->options['deleteBulk'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $this->crud->deleteBulk(
            $this->decodeIdsString($request, $class),
            $options
        );

        return new JsonResponse(null, 204);
    }

    /**
     * @ApiDoc(
     *     description="Copy an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     },
     *     response={"$array"}
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function copyBulkAction(Request $request, $class)
    {
        $query = $request->query->all();

        $options = $this->options['copyBulk'];
        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $copies = $this->crud->copyBulk(
            $this->decodeIdsString($request, $class),
            $options
        );

        return new JsonResponse(array_map(function ($copy) use ($options) {
            return $this->serializer->serialize($copy, $this->options['get']);
        }, $copies), 200);
    }

    /**
     * @ApiDoc(
     *     description="Display the current informations",
     * )
     *
     * @param string $class
     *
     * @return JsonResponse
     */
    public function docAction($class)
    {
        return new JsonResponse($this->routerDocumentator->documentClass($class));
    }

    /**
     * @param string     $class
     * @param string|int $id
     *
     * @return object|null
     */
    protected function find($class, $id)
    {
        if (!is_numeric($id) && property_exists($class, 'uuid')) {
            return $this->om->getRepository($class)->findOneBy(['uuid' => $id]);
        }

        return $this->om->getRepository($class)->findOneBy(['id' => $id]);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return [
            'list' => [Options::SERIALIZE_LIST],
            'get' => [],
            'create' => [],
            'update' => [],
            'deleteBulk' => [],
            'copyBulk' => [],
            'exist' => [],
            'schema' => [],
            'find' => [],
            'doc' => [],
            'export' => [],
        ];
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return [
            'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|csv|\/)).*'],
            'update' => ['id' => '^(?!.*(schema|parameters|find|doc|csv|\/)).*'],
            'exist' => [],
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultHiddenFilters()
    {
        return [];
    }

    public function getClass()
    {
        return null;
    }

    public function getIgnore()
    {
        return [];
    }
}
