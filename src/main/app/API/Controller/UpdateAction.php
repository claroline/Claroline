<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

trait UpdateAction
{
    use CrudAction;

    abstract protected function getObjectManager(): ObjectManager;

    abstract protected function decodeRequest(Request $request): mixed;

    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
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
