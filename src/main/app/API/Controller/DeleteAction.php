<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

trait DeleteAction {
    use CrudAction;
    use RequestDecoderTrait;

    /**
     * @Route("/", name="delete", methods={"DELETE"})
     *
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

        $this->getCrud()->deleteBulk(
            $this->decodeIdsString($request, static::getClass()),
            $options['deleteBulk'] ?? []
        );

        return new JsonResponse(null, 204);
    }
}