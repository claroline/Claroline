<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages platform uploaded files... sort of.
 *
 * @Route("/public_file")
 */
class FileController extends AbstractCrudController
{
    public function getClass(): string
    {
        return PublicFile::class;
    }

    public function getIgnore(): array
    {
        return ['update', 'exist', 'list', 'copyBulk'];
    }

    public function getName(): string
    {
        return 'public_file';
    }

    /**
     * @Route("/upload", name="apiv2_file_upload", options={"method_prefix" = false}, methods={"POST"})
     */
    public function uploadAction(Request $request): JsonResponse
    {
        $files = $request->files->all();

        $objects = [];
        foreach ($files as $file) {
            $object = $this->crud->create(PublicFile::class, [], ['file' => $file, Crud::THROW_EXCEPTION]);

            $objects[] = $this->serializer->serialize($object);
        }

        return new JsonResponse($objects, 200);
    }
}
