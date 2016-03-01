<?php

namespace Innova\VideoRecorderBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\HttpFoundation\File\File as sFile;
use Symfony\Component\Filesystem\Filesystem;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

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
     * @param ResourceManager     $rm
     * @param String              $fileDir
     * @param String              $uploadDir
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
     * Handle web rtc blob file upload, conversion and Claroline File resource creation
     * @param type $postData
     * @param UploadedFile $blob
     * @param Workspace $workspace
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
            $targetDir = $this->fileDir . DIRECTORY_SEPARATOR . $this->tokenStorage->getToken()->getUsername();
        }
        // if the taget dir does not exist, create it
        $fs = new Filesystem();
        if (!$fs->exists($targetDir)) {
          $fs->mkdir($targetDir);
        }

        $doEncode = isset($postData['convert']) && $postData['convert'] == true;
        $isFirefox = $postData['nav'] === 'firefox';
        $extension = 'webm';//$isFirefox ? 'ogg' : 'wav';
        $encodingExt = 'webm';//'mp3';
        $mimeType = 'video/webm';//$doEncode ? 'video/' . $encodingExt : 'video/' . $extension;

        if (!$this->validateParams($postData, $blob)) {
            array_push($errors, 'one or more request parameters are missing.');
            return array('file' => null, 'errors' => $errors);
        }

        // the filename that will be in database (a human readable one should be better)
        //$fileBaseName = $this->claroUtils->generateGuid();
        $fileBaseName = $postData['fileName'];
        $uniqueBaseName = $this->claroUtils->generateGuid();
        $fileName = $uniqueBaseName . '.webm';// . $extension;


        $baseHashName = $this->getBaseFileHashName($uniqueBaseName, $workspace);
        $hashName = $baseHashName . '.webm';//$doEncode ? $baseHashName . '.' . $encodingExt : $baseHashName . '.' . $extension;
        // file size @ToBe overriden if doEncode = true
        $size = $blob->getSize();

        /*if ($doEncode) {
            // the filename after encoding
            $encodedName = $uniqueBaseName . '.' . $encodingExt;
            // upload original file in temp upload (ie web/uploads) dir
            $blob->move($this->tempUploadDir, $fileName);

            // encode original file to mp3
            $cmd = 'avconv -i ' . $this->tempUploadDir . DIRECTORY_SEPARATOR . $fileName . ' -acodec libmp3lame -ab 128k ' . $this->tempUploadDir . DIRECTORY_SEPARATOR . $encodedName;
            $output;
            $returnVar;
            exec($cmd, $output, $returnVar);

            // cmd error
            if ($returnVar !== 0) {
                array_push($errors, 'File conversion failed with command ' . $cmd . ' and returned ' . $returnVar);
                return array('file' => null, 'errors' => $errors);
            }

            // copy the encoded file to user workspace directory
            $fs->copy($this->tempUploadDir . DIRECTORY_SEPARATOR . $encodedName, $targetDir . DIRECTORY_SEPARATOR . $encodedName);
            // get encoded file size...
            $sFile = new sFile($targetDir . DIRECTORY_SEPARATOR . $encodedName);
            $size = $sFile->getSize();
            // remove temp encoded file
            @unlink($this->tempUploadDir . DIRECTORY_SEPARATOR . $encodedName);
            // remove original non encoded file from temp dir
            @unlink($this->tempUploadDir . DIRECTORY_SEPARATOR . $fileName);

        } else {
            $blob->move($targetDir, $fileName);
        }*/

        $blob->move($targetDir, $fileName);

        $file = new File();
        $file->setSize($size);
        //$name = $doEncode ? $fileBaseName.'.'.$encodingExt:$fileBaseName.'.'.$extension;
        $file->setName($fileBaseName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        //return $file;
        return array('file' => $file, 'errors' => []);
    }

    private function getBaseFileHashName($uniqueBaseName, Workspace $workspace = null)
    {
        $hashName = '';
        if (!is_null($workspace)) {
            $hashName = 'WORKSPACE_' . $workspace->getId() . DIRECTORY_SEPARATOR . $uniqueBaseName;
        } else {
            $hashName = $this->tokenStorage->getToken()->getUsername() . DIRECTORY_SEPARATOR . $uniqueBaseName;
        }
        return $hashName;
    }

    /**
     * Checks if the data sent by the Ajax Form contain all mandatory fields
     * @param Array  $postData
     * @param UploadedFile  $file the blob sent by webrtc
     */
    private function validateParams($postData, UploadedFile $file)
    {
        $availableNavs = ["firefox", "chrome"];
        if (!array_key_exists('nav', $postData) || $postData['nav'] === '' || !in_array($postData['nav'], $availableNavs)) {
            return false;
        }

        if(!array_key_exists('fileName', $postData) || !isset($postData['fileName']) || $postData['fileName'] === ''){
            return false;
        }

        if (!isset($file) || $file === null || !$file) {
            return false;
        }
        return true;
    }

}
