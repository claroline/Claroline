<?php

namespace Innova\VideoRecorderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Innova\VideoRecorderBundle\Manager\VideoRecorderManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;

class VideoRecorderController
{

    protected $vrm;

    /**
     * @DI\InjectParams({
     *      "vrm"         = @DI\Inject("innova.video_recorder.manager")
     * })
     */
    public function __construct(VideoRecorderManager $vrm)
    {
        $this->vrm = $vrm;
    }


}
