<?php

namespace Innova\VideoRecorderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Innova\VideoRecorderBundle\Manager\VideoRecorderManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;

class VideoRecorderController
{

    protected $arm;

    /**
     * @DI\InjectParams({
     *      "arm"         = @DI\Inject("innova.video_recorder.manager")
     * })
     */
    public function __construct(VideoRecorderManager $arm)
    {
        $this->arm = $arm;
    }


}
