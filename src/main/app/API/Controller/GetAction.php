<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

trait GetAction
{
    use CrudAction;

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
    #[Route(path: '/{field}/{id}', name: 'get', requirements: ['id' => '.+'], defaults: ['field' => 'id'], methods: ['GET', 'HEAD'])]
    public function getAction(Request $request, string $field, string $id): JsonResponse
    {
        if (Request::METHOD_HEAD === $request->getMethod()) {
            if (!$this->getCrud()->exist(static::getClass(), rawurldecode($id), $field)) {
                throw new NotFoundHttpException(sprintf('No object found for identifier (%s) %s of class %s', $field, $id, static::getClass()));
            }

            return new JsonResponse();
        }

        $object = $this->getCrud()->get(static::getClass(), rawurldecode($id), $field);
        if (!$object) {
            throw new NotFoundHttpException(sprintf('No object found for identifier (%s) %s of class %s', $field, $id, static::getClass()));
        }

        $options = static::getOptions();

        return new JsonResponse(
            $this->getSerializer()->serialize($object, $options['get'] ?? [])
        );
    }
}
