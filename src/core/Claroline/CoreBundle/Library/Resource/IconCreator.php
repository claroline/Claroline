<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;

class IconCreator
{

    /** @var string */
    private $dir;
    /** @var bool */
    private $hasGdExtension;
    /** @var bool */
    private $hasFfmpegExtension;
    /** @var ContainerInterface */
    private $container;
    /** @var EntityManager */
    private $em;

    public function __construct($dir, ContainerInterface $container)
    {
        $this->dir = $dir;

        if (!extension_loaded('gd')) {
            $this->hasGdExtension = false;
        } else {
            $this->hasGdExtension = true;
        }

        if (!extension_loaded('ffmpeg')) {
            $this->hasFfmpegExtension = false;
        } else {
            $this->hasFfmpegExtension = true;
        }

        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    //the end could be refactored: what does imagedestroy should do ? is everything clean ?
    private function createThumbNail($name, $destinationPath, $newWidth, $newHeight)
    {
        if ($this->hasGdExtension) {
            $extension = pathinfo($name, PATHINFO_EXTENSION);

            switch ($extension) {
                case "jpeg":
                    $srcImg = imagecreatefromjpeg($name);
                    $destinationPath = "{$destinationPath}@{$newWidth}x{$newHeight}.png";
                    break;
                case "jpg":
                    $srcImg = imagecreatefromjpeg($name);
                    $destinationPath = "{$destinationPath}@{$newWidth}x{$newHeight}.png";
                    break;
                case "png":
                    $srcImg = imagecreatefrompng($name);
                    $destinationPath = "{$destinationPath}@{$newWidth}x{$newHeight}.png";
                    break;
                case "mov":
                    $srcImg = $this->createMpegGDI($name);
                    $destinationPath = "{$destinationPath}@{$newWidth}x{$newHeight}.png";
                    break;
                case "mp4":
                    $srcImg = $this->createMpegGDI($name);
                    $destinationPath = "{$destinationPath}@{$newWidth}x{$newHeight}.png";
                    break;
                default:
                    return null;
            }

            $this->getFormatedImg($newWidth, $newHeight, $srcImg, $destinationPath);

            imagedestroy($srcImg);

            return $destinationPath;
        } else {
            return null;
        }
    }

    private function getFormatedImg($newWidth, $newHeight, $srcImg, $filename)
    {
        $oldX = imagesx($srcImg);
        $oldY = imagesy($srcImg);

        if ($oldX > $oldY) {
            $thumbWidth = $newWidth;
            $thumbHeight = $oldY * ($newHeight / $oldX);
        } else {
            if ($oldX <= $oldY) {
                $thumbWidth = $oldX * ($newWidth / $oldY);
                $thumbHeight = $newHeight;
            }
        }

        //white background
        $dstImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
        $bg = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $bg);

        //resizing
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $oldX, $oldY);
        $srcImg = imagepng($dstImg, $filename);

        //free memory
        imagedestroy($dstImg);
    }

    private function createMpegGDI($name)
    {
        if ($this->hasFfmpegExtension) {
            $media = new \ffmpeg_movie($name);
            $frameCount = $media->getFrameCount();
            $frame = $media->getFrame(round($frameCount / 2));
            $gdImage = $frame->toGDImage();

            return $gdImage;
        }
    }

    /**
     * Sets the correct ResourceIcon to the resource. Persist the resource is required
     * before firing this.
     *
     * @param AbstractResource $resource
     * @param ResourceType $type
     * @param string $name (required if it's a file)
     * @param
     */
    public function setResourceIcon(AbstractResource $resource, ResourceType $type, $mimeType = null)
    {
        if ($type->getType() !== 'file') {
            $imgs = $this->getTypeIcon($type);
        } else {
            $imgs = $this->getFileIcon($resource, $mimeType);
        }

        $resource->setIcon($imgs);

        return $resource;
    }

    private function getFileIcon($resource, $mimeType)
    {
        if ($mimeType === null) {
            throw new \Exception("no mimeType specified for the file: {$resource->getId()}");
        }
        $mimeElements = explode('/', $mimeType);
        //if video or img => generate the thumbnail, otherwise find an existing one.
        if ($mimeElements[0] === 'video' || $mimeElements[0] === 'image') {

            $originalPath = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $resource->getHashName();
            $newPath = $this->container->getParameter('claroline.thumbnails.directory') . DIRECTORY_SEPARATOR . $this->container->get('claroline.resource.utilities')->generateGuid();
            $generatedFilePath = $this->createThumbNail($originalPath, $newPath, 100, 100);
            $generatedFile = pathinfo($generatedFilePath, PATHINFO_FILENAME);
            $iconName = 'thumbnails' . DIRECTORY_SEPARATOR . $generatedFile;
            $imgs = new ResourceIcon();
            if ($generatedFilePath !== null) {
                $generatedIconType = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\IconType')->find(IconType::GENERATED);
                $imgs->setIconType($generatedIconType);
                $imgs->setLargeIcon($iconName);
                //null for now
                $imgs->setType('generated');
                $imgs->setSmallIcon(null);
                $this->em->persist($imgs);
                $this->em->flush();
            } else {
                $imgs = $this->searchFileIcon($mimeType);
            }
        } else {
            $imgs = $this->searchFileIcon($mimeType);
        }

        return $imgs;
    }

    private function getTypeIcon(ResourceType $type)
    {
            $imgs = $repo->findOneBy(array('type' => $type->getType(), 'iconType' => IconType::TYPE));
            if ($imgs === null) {
                $imgs = $repo->findOneBy(array('type' => 'default', 'iconType' => IconType::DEFAULT_ICON));
            }

            return $imgs;
    }

    private function searchFileIcon($mimeType)
    {
        $mimeElements = explode('/', $mimeType);
        $repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');

        $imgs = $repo->findOneBy(array('type' => $mimeType, 'iconType' => IconType::COMPLETE_MIME_TYPE));

        if ($imgs === null) {
            $imgs = $repo->findOneBy(array('type' => $mimeElements[0], 'iconType' => IconType::BASIC_MIME_TYPE));
            if ($imgs === null) {
                $imgs = $repo->findOneBy(array('type' => 'file', 'iconType' => IconType::TYPE));
            }
        }

        return $imgs;
    }

}