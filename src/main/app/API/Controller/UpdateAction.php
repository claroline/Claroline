<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

trait UpdateAction {
    use CrudAction;
    use RequestDecoderTrait;

    abstract protected function getObjectManager(): ObjectManager;

    /**
     * @Route("/{id}", name="update", requirements={"id"=".+"}, methods={"PUT"})
     *
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

        $object = $this->getCrud()->update(static::getClass(), $data, $options['update'] ?? []);

        // just in case so we really returns the proper object
        $this->getObjectManager()->refresh($object);

        return new JsonResponse(
            $this->getSerializer()->serialize($object, $options['get'] ?? [])
        );
    }
}
