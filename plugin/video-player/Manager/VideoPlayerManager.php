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
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\VideoPlayerBundle\Entity\Track;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.video_player_manager")
 */
class VideoPlayerManager
{
    private $om;
    private $fileDir;
    private $utils;
    private $fileManager;

    /**
     * @DI\InjectParams({
     *      "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *      "fileDir"     = @DI\Inject("%claroline.param.files_directory%"),
     *      "utils"       = @DI\Inject("claroline.utilities.misc"),
     *      "fileManager" = @DI\Inject("claroline.manager.file_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $fileDir,
        ClaroUtilities $utils,
        FileManager $fileManager
    ) {
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->utils = $utils;
        $this->fileManager = $fileManager;
    }

    public function createTrack(File $video, UploadedFile $trackData, $lang, $label, $isDefault = false, $kind = 'subtitles')
    {
        $this->om->startFlushSuite();

        $trackFile = $this->fileManager->create(
            new File(),
            $trackData,
            $trackData->getClientOriginalName(),
            $trackData->getMimeType(),
            $video->getResourceNode()->getWorkspace()
        );
        $this->om->persist($trackFile);

        $track = new Track();
        $track->setVideo($video);
        $track->setLang($lang);
        $track->setKind('subtitles');
        $track->setIsDefault($isDefault);
        $track->setTrackFile($trackFile);
        $track->setLabel($label);
        $this->om->persist($track);
        $this->om->endFlushSuite();

        return $track;
    }

    public function editTrack(Track $track, $lang, $label, $isDefault = false, $kind = 'subtitles')
    {
        $track->setLang($lang);
        $track->setKind('subtitles');
        $track->setIsDefault($isDefault);
        $track->setLabel($label);
        $this->om->persist($track);
        $this->om->flush();

        return $track;
    }

    public function removeTrack(Track $track)
    {
        $this->removeTrackFile($track);
        $this->om->remove($track);
        $this->om->flush();
    }

    public function getTracksByVideo(File $video)
    {
        return $this->om->getRepository('Claroline\VideoPlayerBundle\Entity\Track')->findByVideo($video);
    }

    private function removeTrackFile(Track $track)
    {
        $path = $this->fileDir.DIRECTORY_SEPARATOR.$track->getTrackFile()->getHashName();
        if (file_exists($path)) {
            unlink($path);
        }

        $this->om->remove($track->getTrackFile());
    }
}
