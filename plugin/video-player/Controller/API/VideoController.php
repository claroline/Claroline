<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Controller\API;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\VideoPlayerBundle\Entity\Track;
use Claroline\VideoPlayerBundle\Manager\VideoPlayerManager;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @NamePrefix("api_")
 */
class VideoController extends FOSRestController
{
    private $authorization;
    private $videoManager;
    private $request;
    private $fileDir;

    /**
     * @DI\InjectParams({
     *     "videoManager"  = @DI\Inject("claroline.manager.video_player_manager"),
     *     "request"       = @DI\Inject("request"),
     *     "fileDir"       = @DI\Inject("%claroline.param.files_directory%"),
     *     "authorization" = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(VideoPlayerManager $videoManager, Request $request, $fileDir, AuthorizationCheckerInterface $authorization)
    {
        $this->videoManager = $videoManager;
        $this->request = $request;
        $this->fileDir = $fileDir;
        $this->authorization = $authorization;
    }

    /**
     * @Post("/video/{video}/track", name="post_video_track", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_resource"})
     */
    public function postTrackAction(File $video)
    {
        $this->throwExceptionIfNotGranted($video, 'EDIT');

        $track = $this->request->request->get('track');
        $isDefault = isset($track['is_default']) ? $track['is_default'] : false;
        $fileBag = $this->request->files->get('track');

        return $this->videoManager->createTrack($video, $fileBag['track'], $track['lang'], $track['label'], $isDefault);
    }

    /**
     * @Put("/video/track/{track}", name="put_video_track", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_resource"})
     */
    public function putTrackAction(Track $track)
    {
        $this->throwExceptionIfNotGranted($track->getVideo(), 'EDIT');
        $data = $this->request->request->get('track');
        $isDefault = isset($data['is_default']) ? $data['is_default'] : false;

        return $this->videoManager->editTrack($track,  $data['lang'], $data['label'], $isDefault);
    }

    /**
     * @Get("/video/{video}/tracks", name="get_video_tracks", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_resource"})
     */
    public function getTracksAction(File $video)
    {
        $this->throwExceptionIfNotGranted($video, 'OPEN');

        return $this->videoManager->getTracksByVideo($video);
    }

    /**
     * @Delete("/video/track/{track}", name="delete_video_track", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_video"})
     */
    public function deleteTrackAction(Track $track)
    {
        $this->throwExceptionIfNotGranted($track->getVideo(), 'EDIT');

        $this->videoManager->removeTrack($track);

        return [];
    }

    /**
     * @Get("/video/track/{track}/stream", name="get_video_track_stream", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_video"})
     */
    public function streamTrackAction(Track $track)
    {
        $this->throwExceptionIfNotGranted($track->getVideo(), 'OPEN');
        $file = $track->getTrackFile();

        return $this->returnFile($file);
    }

    /**
     * @deprecated only kept for HTML contents that embed videos using this URL
     *
     * @Get("/video/{video}/stream", name="get_video_stream", options={ "method_prefix" = false })
     * @View(serializerGroups={"api_video"})
     */
    public function streamVideoAction(File $video)
    {
        $this->throwExceptionIfNotGranted($video, 'OPEN');

        return $this->returnFile($video);
    }

    private function returnFile(File $file)
    {
        // see https://github.com/claroline/CoreBundle/commit/7cee6de85bbc9448f86eb98af2abb1cb072c7b6b
        $this->get('session')->save();
        $path = $this->fileDir.DIRECTORY_SEPARATOR.$file->getHashName();
        $response = new BinaryFileResponse($path);

        return $response;
    }

    private function throwExceptionIfNotGranted(File $video, $permission)
    {
        $collection = new ResourceCollection([$video->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
