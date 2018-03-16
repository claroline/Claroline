<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.video_player_manager")
 */
class VideoPlayerManager
{
    private $om;

    /**
     * @DI\InjectParams({
     *      "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getTracksByVideo(File $video)
    {
        return $this->om->getRepository('Claroline\VideoPlayerBundle\Entity\Track')->findByVideo($video);
    }
}
