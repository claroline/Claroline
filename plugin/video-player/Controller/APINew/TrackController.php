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

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(class="Claroline\VideoPlayerBundle\Entity\Track")
 * @Route("/videotrack")
 */
class TrackController extends AbstractCrudController
{
    private $fileDir;

    /**
     * @DI\InjectParams({
     *      "fileDir" = @DI\Inject("%claroline.param.files_directory%")
     * })
     */
    public function __construct($fileDir)
    {
        $this->fileDir = $fileDir;
    }

    /**
     * @param Request $request
     * @param string  $class
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
     * @param Request $request
     * @param string  $class
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

    public function getName()
    {
        return 'video_track';
    }
}
