<?php

namespace Innova\AudioRecorderBundle\Manager;

use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\HttpFoundation\File\File as sFile;
use Symfony\Component\Filesystem\Filesystem;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Innova\AudioRecorderBundle\Entity\AudioRecorderConfiguration;

/**
 * @DI\Service("innova.audio_recorder.manager")
 */
class AudioRecorderManager
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
     * @param UploadedFile $blob
     * @param Workspace    $workspace
     *
     * @return File
     */
    public function uploadFileAndCreateResource($postData, UploadedFile $blob, Workspace $workspace = null)
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
        $extension = $isFirefox ? 'ogg' : 'wav';
        $encodingExt = 'mp3';
        $mimeType = 'audio/'.$encodingExt;

        if (!$this->validateParams($postData, $blob)) {
            array_push($errors, 'one or more request parameters are missing.');

            return array('file' => null, 'errors' => $errors);
        }

        $fileBaseName = $postData['fileName'];
        $uniqueBaseName = $this->claroUtils->generateGuid();
        $hashName = $this->getBaseFileHashName($uniqueBaseName, $workspace).'.'.$encodingExt;

        $tempAudioFileName = $fileBaseName.'.'.$extension;
        $finalFileName = $uniqueBaseName.'.'.$encodingExt;

        // upload original file in temp upload (ie web/uploads) dir
        $blob->move($this->tempUploadDir, $tempAudioFileName);

        $sourceFilePath = $this->tempUploadDir.DIRECTORY_SEPARATOR.$tempAudioFileName;
        $tempEncodedFilePath = $this->tempUploadDir.DIRECTORY_SEPARATOR.$finalFileName;

        // encode original file (ogg/wav) to mp3
        $cmd = 'avconv -i '.$sourceFilePath.' -acodec libmp3lame -ab 128k '.$tempEncodedFilePath;
        exec($cmd, $output, $returnVar);

        // cmd error
        if ($returnVar !== 0) {
            array_push($errors, 'File conversion failed with command '.$cmd.' and returned '.$returnVar);

            return array('file' => null, 'errors' => $errors);
        }

        // copy the encoded file to user workspace directory
        $fs->copy($tempEncodedFilePath, $targetDir.DIRECTORY_SEPARATOR.$finalFileName);
        // get encoded file size...
        $sFile = new sFile($targetDir.DIRECTORY_SEPARATOR.$finalFileName);
        $size = $sFile->getSize();
        // remove temp encoded file
        @unlink($tempEncodedFilePath);
        // remove original non encoded file from temp dir
        @unlink($sourceFilePath);

        $file = new File();
        $file->setSize($size);
        $file->setName($fileBaseName.'.'.$encodingExt);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        return array('file' => $file, 'errors' => []);
    }

    private function getBaseFileHashName($uniqueBaseName, Workspace $workspace = null)
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
     * @param UploadedFile $file     the blob sent by webrtc
     */
    private function validateParams($postData, UploadedFile $file)
    {
        $availableNavs = ['firefox', 'chrome'];
        if (!array_key_exists('nav', $postData) || $postData['nav'] === '' || !in_array($postData['nav'], $availableNavs)) {
            return false;
        }

        if (!array_key_exists('fileName', $postData) || !isset($postData['fileName']) || $postData['fileName'] === '') {
            return false;
        }

        if (!isset($file) || $file === null || !$file) {
            return false;
        }

        return true;
    }

    public function updateConfiguration(AudioRecorderConfiguration $config, $postData)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $config->setMaxTry($postData['max_try']);
        $config->setMaxRecordingTime($postData['max_recording_time']);
        $om->persist($config);
        $om->flush();
    }

    public function getConfig()
    {
        $config = $this->container->get('doctrine.orm.entity_manager')->getRepository('InnovaAudioRecorderBundle:AudioRecorderConfiguration')->findAll()[0];

        return $config;
    }
}
