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
    /** @var StrictDispatcher */
    private $dispatcher;

    /**
     * FileController constructor.
     *
     * @param StrictDispatcher $dispatcher
     */
    public function __construct(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
        return 'public_file';
    }

    /**
     * @Route("/upload", name="apiv2_file_upload", options={"method_prefix" = false}, methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadAction(Request $request)
    {
        $files = $request->files->all();
        $handler = $request->get('handler');

        $objects = [];
        foreach ($files as $file) {
            $object = $this->crud->create(PublicFile::class, [], ['file' => $file]);

            $this->dispatcher->dispatch(strtolower('upload_file_'.$handler), 'File\UploadFile', [$object]);
            $objects[] = $this->serializer->serialize($object);
        }

        return new JsonResponse($objects, 200);
    }
}
