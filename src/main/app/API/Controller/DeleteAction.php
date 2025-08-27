<?php

namespace Claroline\AppBundle\API\Controller;

use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

trait DeleteAction
{
    use CrudAction;

    abstract protected function getObjectManager(): ObjectManager;

    abstract protected function decodeRequest(Request $request): mixed;

    #[Route(path: '', name: 'delete', methods: ['DELETE'])]
    public function deleteBulkAction(Request $request): JsonResponse
    {
        $options = static::getOptions();

        $ids = $this->decodeRequest($request);
        $objects = $this->getObjectManager()->getRepository(static::getClass())->findBy(['uuid' => $ids]);

        if (empty($objects)) {
            throw new NotFoundHttpException('No object found.');
        }

        $this->getCrud()->deleteBulk($objects, $options['delete'] ?? []);

        return new JsonResponse(null, 204);
    }
}
