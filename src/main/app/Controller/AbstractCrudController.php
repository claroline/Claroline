<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class AbstractCrudController
{
    use RequestDecoderTrait;

    protected ObjectManager $om;
    protected SerializerProvider $serializer;
    protected Crud $crud;

    /**
     * Get the name of the managed entity.
     */
    abstract public function getName(): string;

    /**
     * Get the name of the managed entity.
     */
    abstract public function getClass(): string;

    public function setSerializer(SerializerProvider $serializer): void
    {
        $this->serializer = $serializer;
    }

    public function setCrud(Crud $crud): void
    {
        $this->crud = $crud;
    }

    public function setObjectManager(ObjectManager $om): void
    {
        $this->om = $om;
    }

    /**
     * @ApiDoc(
     *     description="Finds an object class $class.",
     *     parameters={
     *          {"name": "field", "type": "string", "description": "The name of the identifier we want to use (eg. id, slug)"},
     *          {"name": "id", "type": {"string"}, "description": "The object identifier value"}
     *     },
     *     response={"$object"}
     * )
     */
    public function getAction(Request $request, string $field, string $id): JsonResponse
    {
        if (Request::METHOD_HEAD === $request->getMethod()) {
            if (!$this->crud->exist(static::getClass(), rawurldecode($id), $field)) {
                throw new NotFoundHttpException(sprintf('No object found for identifier (%s) %s of class %s', $field, $id, static::getClass()));
            }

            return new JsonResponse();
        }

        $object = $this->crud->get(static::getClass(), rawurldecode($id), $field);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('No object found for identifier (%s) %s of class %s', $field, $id, static::getClass()));
        }

        $options = static::getOptions();

        return new JsonResponse(
            $this->serializer->serialize($object, $options['get'] ?? [])
        );
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
     */
    public function listAction(Request $request): JsonResponse
    {
        $options = static::getOptions();

        return new JsonResponse(
            $this->crud->list(static::getClass(), array_merge([], $request->query->all(), [
                'hiddenFilters' => $this->getDefaultHiddenFilters(),
            ]), $options['list'] ?? [])
        );
    }

    /**
     * @ApiDoc(
     *     description="Create an object class $class.",
     *     body={
     *         "schema":"$schema"
     *     },
     *     response={"$object"}
     * )
     */
    public function createAction(Request $request): JsonResponse
    {
        $options = static::getOptions();

        $object = $this->crud->create(static::getClass(), $this->decodeRequest($request), $options['create'] ?? []);

        return new JsonResponse(
            $this->serializer->serialize($object, $options['get'] ?? []),
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
     *          {"name": "id", "type": {"string"}, "description": "The object uuid"}
     *     },
     *     response={"$object"}
     * )
     */
    public function updateAction(string $id, Request $request): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (!isset($data['id'])) {
            $data['id'] = $id;
        }

        $options = static::getOptions();

        $object = $this->crud->update(static::getClass(), $data, $options['update'] ?? []);

        // just in case so we really returns the proper object
        $this->om->refresh($object);

        return new JsonResponse(
            $this->serializer->serialize($object, $options['get'] ?? [])
        );
    }

    /**
     * @ApiDoc(
     *     description="Remove an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string"}, "description": "The object uuid."}
     *     }
     * )
     */
    public function deleteBulkAction(Request $request): JsonResponse
    {
        $options = static::getOptions();

        $this->crud->deleteBulk(
            $this->decodeIdsString($request, static::getClass()),
            $options['deleteBulk'] ?? []
        );

        return new JsonResponse(null, 204);
    }

    public static function getOptions(): array
    {
        return [
            'get' => [],
            'list' => [Options::SERIALIZE_LIST],
            'create' => [],
            'update' => [],
        ];
    }

    protected function getDefaultHiddenFilters(): array
    {
        return [];
    }

    public function getIgnore(): array
    {
        return [];
    }
}
