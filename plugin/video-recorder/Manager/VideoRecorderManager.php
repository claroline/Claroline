<?php

namespace Innova\VideoRecorderBundle\Manager;

use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\HttpFoundation\File\File as sFile;
use Symfony\Component\Filesystem\Filesystem;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\VideoRecorderBundle\Entity\VideoRecorderConfiguration;

/**
 * @DI\Service("innova.video_recorder.manager")
 */
class VideoRecorderManager
{
    protected $rm;
    protected $fileDir;
    protected $tempUploadDir;
    protected $tokenStorage;
    protected $claroUtils;
    protected $container;
    protected $workspaceManager;

    /**
     * @DI\InjectParams({
     *      "container"   = @DI\Inject("service_container"),
     *      "rm"          = @DI\Inject("claroline.manager.resource_manager"),
     *      "fileDir"     = @DI\Inject("%claroline.param.files_directory%"),
     *      "uploadDir"   = @DI\Inject("%claroline.param.uploads_directory%")
     * })
     *
     * @param ResourceManager $rm
     * @param string          $fileDir
     * @param string          $uploadDir
     */
    public function __construct(ContainerInterface $container, ResourceManager $rm, $fileDir, $uploadDir)
    {
        $this->rm = $rm;
        $this->container = $container;
        $this->fileDir = $fileDir;
        $this->tempUploadDir = $uploadDir;
        $this->tokenStorage = $container->get('security.token_storage');
        $this->claroUtils = $container->get('claroline.utilities.misc');
        $this->workspaceManager = $container->get('claroline.manager.workspace_manager');
    }

    /**
     * Handle web rtc blob file upload, conversion and Claroline File resource creation.
     *
     * @param type         $postData
     * @param UploadedFile $video
     * @param Workspace    $workspace
     *
     * @return Claroline File
     */
    public function uploadFileAndCreateResource($postData, UploadedFile $video, Workspace $workspace = null)
    {
        $errors = array();
        // final file upload dir
        $targetDir = '';
        if (!is_null($workspace)) {
            $targetDir = $this->workspaceManager->getStorageDirectory($workspace);
        } else {
            $targetDir = $this->fileDir.DIRECTORY_SEPARATOR.$this->tokenStorage->getToken()->getUsername();
        }
        // if the taget dir does not exist, create it
        $fs = new Filesystem();
        if (!$fs->exists($targetDir)) {
            $fs->mkdir($targetDir);
        }

        $isFirefox = isset($postData['nav']) && $postData['nav'] === 'firefox';
        $extension = 'webm';
        $encodingExt = 'webm';
        $mimeType = 'video/'.$extension;

        if (!$this->validateParams($postData, $video)) {
            array_push($errors, 'one or more request parameters are missing.');

            return array('file' => null, 'errors' => $errors);
        }

        // the filename that will be in database (human readable)
        $fileBaseName = $postData['fileName'];
        $uniqueBaseName = $this->claroUtils->generateGuid();
        $finalFileName = $uniqueBaseName.'.'.$extension;
        $finalFilePath = $targetDir.DIRECTORY_SEPARATOR.$finalFileName;

        $baseHashName = $this->getBaseFileHashName($uniqueBaseName, $workspace);
        $hashName = $baseHashName.'.'.$extension;

        $tempVideoFileName = $fileBaseName.'.'.$extension;

        $encode = true;

        if ($encode) {
            // upload file to temp directory to allow it's conversion
          $video->move($this->tempUploadDir, $tempVideoFileName);
            $sourceFilePath = $this->tempUploadDir.DIRECTORY_SEPARATOR.$tempVideoFileName;
            $tempEncodedFilePath = $this->tempUploadDir.DIRECTORY_SEPARATOR.$finalFileName;
          // create avconv cmd
          $cmd = 'avconv -i '.$sourceFilePath.' -codec:v copy -codec:a opus -ac 1 '.$tempEncodedFilePath;
            $output;
            $returnVar;
            exec($cmd, $output, $returnVar);

          // cmd error
          if ($returnVar !== 0) {
              array_push($errors, 'File conversion failed with command '.$cmd.' and returned '.$returnVar);

              return array('file' => null, 'errors' => $errors);
          }

            $fs->copy($tempEncodedFilePath, $finalFilePath);

          // get encoded file size...
          $sFile = new sFile($finalFilePath);
            $size = $sFile->getSize();
          // remove temp encoded file from temp dir
          @unlink($tempEncodedFilePath);
          // remove source file from temp dir
          @unlink($sourceFilePath);
        } else {
            $size = $video->getSize();
            $video->move($targetDir, $finalFileName);
        }

        $file = new File();
        $file->setSize($size);
        $file->setName($fileBaseName.'.'.$encodingExt);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        return array('file' => $file, 'errors' => []);
    }

    public function getBaseFileHashName($uniqueBaseName, Workspace $workspace = null)
    {
        $hashName = '';
        if (!is_null($workspace)) {
            $hashName = 'WORKSPACE_'.$workspace->getId().DIRECTORY_SEPARATOR.$uniqueBaseName;
        } else {
            $hashName = $this->tokenStorage->getToken()->getUsername().DIRECTORY_SEPARATOR.$uniqueBaseName;
        }

        return $hashName;
    }

    /**
     * Checks if the data sent by the Ajax Form contain all mandatory fields.
     *
     * @param array        $postData
     * @param UploadedFile $video     the video or video + audio blob sent by webrtc
     * @param bool         $isFirefox
     * @param UploadedFile $audio     the audio blob sent by webrtc if chrome has been used
     */
    public function validateParams($postData, UploadedFile $video)
    {
        if (!array_key_exists('fileName', $postData) || !isset($postData['fileName']) || $postData['fileName'] === '') {
            return false;
        }

        if (!isset($video) || $video === null || !$video) {
            return false;
        }

        return true;
    }

    public function updateConfiguration(VideoRecorderConfiguration $config, $postData)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $config->setMaxRecordingTime($postData['max_recording_time']);
        $om->persist($config);
        $om->flush();
    }

    public function getConfig()
    {
        $config = $this->container->get('doctrine.orm.entity_manager')->getRepository('InnovaVideoRecorderBundle:VideoRecorderConfiguration')->findAll()[0];

        return $config;
    }
}
