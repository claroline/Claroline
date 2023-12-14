<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
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
     * Get the name of the managed entity.
     */
    abstract public function getName(): string;

    public function setFinder(FinderProvider $finder): void
    {
        $this->finder = $finder;
    }

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
            if (!$this->crud->exist($this->getClass(), $id, $field)) {
                throw new NotFoundHttpException(sprintf('No object found for id %s of class %s', $id, $this->getClass()));
            }

            return new JsonResponse();
        }

        $object = $this->crud->get($this->getClass(), $id, $field);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('No object found for id %s of class %s', $id, $this->getClass()));
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
     *
     * @param string $class
     */
    public function listAction(Request $request, $class): JsonResponse
    {
        $options = static::getOptions();

        $query = $request->query->all();
        $query['hiddenFilters'] = $this->getDefaultHiddenFilters();

        return new JsonResponse(
            $this->crud->list($class, $query, $options['list'] ?? [])
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
     *
     * @param string $class
     */
    public function createAction(Request $request, $class): JsonResponse
    {
        $options = static::getOptions();

        $object = $this->crud->create($class, $this->decodeRequest($request), $options['create'] ?? []);
        if (is_array($object)) {
            return new JsonResponse($object, 422);
        }

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
     *
     * @param string $id
     * @param string $class
     */
    public function updateAction($id, Request $request, $class): JsonResponse
    {
        $data = $this->decodeRequest($request);
        if (!isset($data['id'])) {
            $data['id'] = $id;
        }

        $options = static::getOptions();

        $object = $this->crud->update($class, $data, $options['update'] ?? []);
        if (is_array($object)) {
            return new JsonResponse($object, 422);
        }

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
     *
     * @param string $class
     */
    public function deleteBulkAction(Request $request, $class): JsonResponse
    {
        $options = static::getOptions();

        $this->crud->deleteBulk(
            $this->decodeIdsString($request, $class),
            $options['deleteBulk'] ?? []
        );

        return new JsonResponse(null, 204);
    }

    public static function getOptions(): array
    {
        return [
            'get' => [],
            'list' => [Options::SERIALIZE_LIST],
            'create' => [Crud::THROW_EXCEPTION],
            'update' => [Crud::THROW_EXCEPTION],
        ];
    }

    public function getRequirements(): array
    {
        return [];
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
