<?php

namespace Innova\MediaResourceBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityManager;
use Innova\MediaResourceBundle\Entity\Media;
use Innova\MediaResourceBundle\Entity\MediaResource;
use Innova\MediaResourceBundle\Entity\Options;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("innova_media_resource.manager.media_resource")
 */
class MediaResourceManager
{
    protected $em;
    protected $translator;
    protected $fileDir;
    protected $uploadDir;
    protected $claroUtils;
    protected $container;
    protected $workspaceManager;

    /**
     * @DI\InjectParams({
     *      "container"   = @DI\Inject("service_container"),
     *      "em"          = @DI\Inject("doctrine.orm.entity_manager"),
     *      "translator"  = @DI\Inject("translator"),
     *      "fileDir"     = @DI\Inject("%claroline.param.files_directory%"),
     *      "uploadDir"   = @DI\Inject("%claroline.param.uploads_directory%")
     * })
     *
     * @param ContainerInterface  $container
     * @param EntityManager       $em
     * @param TranslatorInterface $translator
     * @param string              $fileDir
     */
    public function __construct(ContainerInterface $container, EntityManager $em, TranslatorInterface $translator, $fileDir, $uploadDir)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->container = $container;
        $this->fileDir = $fileDir;
        $this->uploadDir = $uploadDir;
        $this->claroUtils = $container->get('claroline.utilities.misc');
        $this->workspaceManager = $container->get('claroline.manager.workspace_manager');
    }

    public function getRepository()
    {
        return $this->em->getRepository('InnovaMediaResourceBundle:MediaResource');
    }

    /**
     * Delete associated Media (removing from server hard drive) before deleting the entity.
     *
     * @param MediaResource $mr
     *
     * @return \Innova\MediaResourceBundle\Manager\MediaResourceManager
     */
    public function delete(MediaResource $mr)
    {
        // delete all files from server
        $medias = $mr->getMedias();
        foreach ($medias as $media) {
            $this->removeUpload($media->getUrl());
        }
        $this->em->remove($mr);
        $this->em->flush();

        return $this;
    }

    /**
     * Create default options for newly created MediaResource.
     **/
    public function createMediaResourceDefaultOptions(MediaResource $mr)
    {
        $options = new Options();
        $mr->setOptions($options);
    }

    /**
     * Create default options for newly created MediaResource.
     **/
    public function persist(MediaResource $mr)
    {
        $this->em->persist($mr);
        $this->em->flush();

        return $mr;
    }

    /**
     * Handle MediaResource associated files.
     *
     * @param UploadedFile  $file
     * @param MediaResource $mr
     * @param Workspace     $workspace
     */
    public function handleMediaResourceMedia(UploadedFile $file, MediaResource $mr, Workspace $workspace)
    {
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
        // set new filename
        $ext = $file->getClientOriginalExtension();
        $uniqueBaseName = $this->claroUtils->generateGuid();

        // upload file
        if ($file->move($targetDir, $uniqueBaseName.'.'.$ext)) {
            // create Media Entity
            $media = new Media();
            $media->setType('audio');
            $media->setUrl('WORKSPACE_'.$workspace->getId().DIRECTORY_SEPARATOR.$uniqueBaseName.'.'.$ext);
            $mr->addMedia($media);
            $media->setMediaResource($mr);
            unset($file);
        } else {
            $message = $this->translator->trans('error_while_uploading', [], 'media_resource');
            throw new \Exception($message);
        }

        return $mr;
    }

    public function copyMedia(MediaResource $mr, Media $origin)
    {
        $ext = pathinfo($origin->getUrl(), PATHINFO_EXTENSION);
        $newName = $this->claroUtils->generateGuid().'.'.$ext;
        $baseUrl = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR;
        // make a copy of the file
        if (copy($baseUrl.$origin->getUrl(), $baseUrl.$newName)) {
            // duplicate file
            $new = new Media();
            $new->setType($origin->getType());
            $new->setUrl($newName);
            $mr->addMedia($new);
            $this->em->persist($mr);
            $new->setMediaResource($mr);
        }
    }

    public function copyOptions(MediaResource $new, MediaResource $original)
    {
        $originalOptions = $original->getOptions();
        $newOptions = new Options();
        $newOptions->setMode($originalOptions->getMode());
        $newOptions->setTtsLanguage($originalOptions->getTtsLanguage());
        $newOptions->setShowTextTranscription($originalOptions->getShowTextTranscription());
        $new->setOptions($newOptions);
        $this->em->persist($new);
    }

    public function removeUpload($url)
    {
        $fullPath = $this->container->getParameter('claroline.param.files_directory')
           .DIRECTORY_SEPARATOR
           .$url;
        if (file_exists($fullPath)) {
            unlink($fullPath);

            return true;
        } else {
            return false;
        }
    }

    public function exportToZip(MediaResource $resource, $data)
    {
        $files = [];
        // get original file url
        $url = $resource->getMedias()[0]->getUrl();
        $originalFileFullPath = $this->container->getParameter('claroline.param.files_directory')
           .DIRECTORY_SEPARATOR
           .$url;

        // ensure the name is clean
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $resource->getName());
        // create temp_dir
        $tempDir = $this->uploadDir.DIRECTORY_SEPARATOR.$cleanName.'_temp';
        $fs = new Filesystem();
        if (!$fs->exists($tempDir)) {
            $fs->mkdir($tempDir);
        }
        // copy original file
        // get original file extension
        $ext = pathinfo($originalFileFullPath, PATHINFO_EXTENSION);
        $fullFileName = $cleanName.'_full_file.'.$ext;
        $copiedFilePath = $tempDir.DIRECTORY_SEPARATOR.$fullFileName;

        // create srt file
        $vttFile = $tempDir.DIRECTORY_SEPARATOR.$cleanName.'_SRT.vtt';
        $fs->touch($vttFile);
        $vtt = '';
        // make a copy of the file
        if (copy($originalFileFullPath, $copiedFilePath)) {
            // create chuncked audio files in temp dir
            $index = 1;
            array_push($files, $copiedFilePath);

            $vtt .= 'WEBVTT'.PHP_EOL;

            foreach ($data as $region) {
                $start = $region['start'];
                $end = $region['end'];
                $duration = $end - $start;

                // create .vtt line
                $vtt .= PHP_EOL;
                $vtt .= $index.PHP_EOL;
                $vtt .= $this->secondsToSrtTime($start).' --> '.$this->secondsToSrtTime($end).PHP_EOL;
                if ($region['note'] !== '') {
                    $vtt .= strip_tags($region['note']);
                }
                $vtt .= PHP_EOL;
                $partFilePath = $tempDir.DIRECTORY_SEPARATOR.$cleanName.'_part_'.$index.'.'.$ext;
                $cmd = 'ffmpeg -i '.$copiedFilePath.' -ss '.$start.' -t '.$duration.' '.$partFilePath;
                exec($cmd, $output, $returnVar);

                ++$index;
                // cmd success
                if (count($output) === 0 && $returnVar === 0) {
                    array_push($files, $partFilePath);
                } else {
                    // @TODO do something in case of cmd error
                }
            }
            file_put_contents($vttFile, $vtt);
            array_push($files, $vttFile);
        }

        $zipName = $cleanName.'.zip';
        $archive = new \ZipArchive();
        $pathToArchive = $tempDir.DIRECTORY_SEPARATOR.$zipName;
        $archive->open($pathToArchive, \ZipArchive::CREATE);
        foreach ($files as $f) {
            $archive->addFromString(basename($f), file_get_contents($f));
        }
        $archive->close();

        $fs->remove($files);

        return ['zip' => $pathToArchive, 'name' => $zipName, 'tempFolder' => $tempDir];
    }

    private function secondsToSrtTime($seconds)
    {
        $stringSec = (string) $seconds;
        $fullMilli = explode('.', $stringSec);
        $milli = array_key_exists(1, $fullMilli) ? substr($fullMilli[1], 0, 3) : '000';
        $ms = \gmdate('i:s', $seconds);
        // time limit is 00:59:59,999 (less than one hour)
        return '00:'.$ms.'.'.$milli;
    }
}
