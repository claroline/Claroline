<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractCrudController
{
    use RequestDecoderTrait;

    /** @var FinderProvider */
    protected $finder;

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

    /**
     * Get the name of the managed entity.
     */
    abstract public function getName(): string;

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

    /**
     * @ApiDoc(
     *     description="Find a single object of class $class.",
     *     queryString={"$finder"},
     *     response={"$object"}
     * )
     *
     * @param string $class
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
            case 1:
                return new JsonResponse(
                    $this->serializer->serialize($data[0], $options)
                );
            default:
                return new JsonResponse('Multiple results, use "list" instead', 400);
        }
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
     * @param string|int $id
     * @param string     $class
     */
    public function getAction(Request $request, $id, $class): JsonResponse
    {
        $query = $request->query->all();
        $object = $this->crud->get($class, $id);

        $options = $this->options['get'];
        if (isset($query['options'])) {
            $options = $query['options'];
        }

        if (!$object) {
            throw new NotFoundHttpException(sprintf('No object found for id %s of class %s', $id.'', $class));
        }

        return new JsonResponse(
            $this->serializer->serialize($object, $options ?? [])
        );
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
     */
    public function existAction($class, $field, $value): JsonResponse
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
     * @param string $class
     */
    public function listAction(Request $request, $class): JsonResponse
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();

        return new JsonResponse(
            $this->crud->list($class, $query, $options ?? [])
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
     * @param string $class
     *
     * @deprecated use transfer feature instead
     */
    public function csvAction(Request $request, $class): BinaryFileResponse
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, $this->getDefaultHiddenFilters());

        $csvFilename = $this->crud->csv($class, $query, $options ?? []);

        return new BinaryFileResponse($csvFilename, 200, [
            'Content-Type' => 'text/csv',
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
     * @param string $class
     */
    public function createAction(Request $request, $class): JsonResponse
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
     * @param string     $class
     */
    public function updateAction($id, Request $request, $class): JsonResponse
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
     * @param string $class
     */
    public function deleteBulkAction(Request $request, $class): JsonResponse
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
     * @param string $class
     */
    public function copyBulkAction(Request $request, $class): JsonResponse
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

        return new JsonResponse(array_map(function ($copy) {
            return $this->serializer->serialize($copy, $this->options['get']);
        }, $copies), 200);
    }

    public function getOptions(): array
    {
        return [
            'list' => [Options::SERIALIZE_LIST],
            'get' => [],
            'create' => [],
            'update' => [],
            'deleteBulk' => [],
            'copyBulk' => [],
            'exist' => [],
            'find' => [],
        ];
    }

    public function getRequirements(): array
    {
        return [
            'get' => ['id' => '^(?!.*(copy|parameters|find|csv|\/)).*'],
            'update' => ['id' => '^(?!.*(parameters|find|csv|\/)).*'],
            'exist' => [],
        ];
    }

    protected function getDefaultHiddenFilters(): array
    {
        return [];
    }

    public function getClass(): ?string
    {
        return null;
    }

    public function getIgnore(): array
    {
        return [];
    }
}
