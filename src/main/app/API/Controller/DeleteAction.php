<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

trait DeleteAction
{
    use CrudAction;

    abstract protected function decodeIdsString(Request $request, string $class, string $property = 'ids'): array;

    /**
     * @ApiDoc(
     *     description="Remove an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string"}, "description": "The object uuid."}
     *     }
     * )
     */
    #[Route(path: '/', name: 'delete', methods: ['DELETE'])]
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
