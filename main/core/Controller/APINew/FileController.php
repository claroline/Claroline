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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages platform uploaded files... sort of.
 *
 * @EXT\Route("/publicfile")
 */
class FileController extends AbstractCrudController
{
    /**
     * @EXT\Route(
     *    "/upload",
     *    name="apiv2_file_upload",
     *    options={ "method_prefix" = false }
     * )
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $files = $this->uploadFiles($request);
        $data = [];

        foreach ($files as $file) {
            $data[] = $this->serializer->serialize($file);
        }

        return new JsonResponse($data, 200);
    }

    private function uploadFiles(Request $request)
    {
        $files = $request->files->all();
        $handler = $request->get('handler');
        $objects = [];
        /** @var StrictDispatcher */
        $dispatcher = $this->container->get('Claroline\AppBundle\Event\StrictDispatcher');

        foreach ($files as $file) {
            $object = $this->crud->create(
              PublicFile::class,
              [],
              ['file' => $file]
          );

            $dispatcher->dispatch(strtolower('upload_file_'.$handler), 'File\UploadFile', [$object]);
            $objects[] = $object;
        }

        return $objects;
    }

    public function getClass()
    {
        return PublicFile::class;
    }

    public function getIgnore()
    {
        return ['update', 'exist', 'list', 'copyBulk'];
    }

    /** @return string */
    public function getName()
    {
        return 'uploadedfile';
    }
}
