<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Controller\APINew;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/videotrack")
 */
class TrackController extends AbstractCrudController
{
    private $fileDir;

    public function __construct($fileDir)
    {
        $this->fileDir = $fileDir;
    }

    /**
     * @param string $class
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $class)
    {
        $trackData = json_decode($request->request->get('track', false), true);
        $files = $request->files->all();
        $trackData['file'] = $files['file'];

        $object = $this->crud->create(
            $class,
            $trackData,
            $this->options['create']
        );

        if (is_array($object)) {
            return new JsonResponse($object, 400);
        }

        return new JsonResponse(
            $this->serializer->serialize($object, $this->options['get']),
            201
        );
    }

    /**
     * @param string $class
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, $class)
    {
        $tracks = parent::decodeIdsString($request, $class);

        foreach ($tracks as $track) {
            $trackFile = $track->getTrackFile();
            $path = $this->fileDir.DIRECTORY_SEPARATOR.$trackFile->getHashName();

            if (file_exists($path)) {
                unlink($path);
            }

            $this->om->remove($trackFile);
        }

        return parent::deleteBulkAction($request, $class);
    }

    public function getClass()
    {
        return 'Claroline\VideoPlayerBundle\Entity\Track';
    }

    public function getName()
    {
        return 'video_track';
    }
}
