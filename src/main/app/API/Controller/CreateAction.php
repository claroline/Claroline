<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

trait CreateAction {
    use CrudAction;
    use RequestDecoderTrait;

    /**
     * @Route("/", name="create", methods={"POST"})
     *
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

        $object = $this->getCrud()->create(static::getClass(), $this->decodeRequest($request), $options['create'] ?? []);

        return new JsonResponse(
            $this->getSerializer()->serialize($object, $options['get'] ?? []),
            201
        );
    }
}
