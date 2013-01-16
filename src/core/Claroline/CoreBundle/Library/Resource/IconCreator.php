<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        $this->container = $container;
        $this->hasGdExtension = extension_loaded('gd');
        $this->hasFfmpegExtension = extension_loaded('ffmpeg');
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    /**
     *
     * @param string $originalPath
     * @param string $destinationPath
     * @param integer $newWidth
     * @param integer $newHeight
     * @param string $mimeExtension
     * @param string $baseMime
     * @return null
     */
    private function createThumbNail($originalPath, $destinationPath, $newWidth, $newHeight, $mimeExtension, $baseMime)
    {
        if ($this->hasGdExtension) {
            if ($baseMime == 'image' && function_exists($funcname = "imagecreatefrom{$mimeExtension}")) {
                $srcImg = $funcname($originalPath);
            } else {
                switch ($mimeExtension) {
                    case 'jpg':
                        $srcImg = imagecreatefromjpeg($originalPath);
                        break;
                    case 'mov':
                        $srcImg = $this->createMpegGDI($originalPath);
                        break;
                    case 'mp4':
                        $srcImg = $this->createMpegGDI($originalPath);
                        break;
                    default:
                        return null;
                }
            }

            if ($srcImg == null) {
                return null;
            }

            $this->getFormatedImg($newWidth, $newHeight, $srcImg, $destinationPath);
            imagedestroy($srcImg);

            return $destinationPath;
        }

        return null;
    }

    /**
     * Resize an image.
     *
     * @param string $newWidth
     * @param string $newHeight
     * @param string $srcImg
     * @param string $filename
     */
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

    /**
     * Create an mpeg image from a video.
     *
     * @param string $originalPath
     *
     * @return string
     */
    private function createMpegGDI($originalPath)
    {
        $image = null;

        if ($this->hasFfmpegExtension) {
            $media = new \ffmpeg_movie($originalPath);
            $frameCount = $media->getFrameCount();
            $frame = $media->getFrame(round($frameCount / 2));
            $image = $frame->toGDImage();

        }

        return $image;
    }

    /**
     * Sets the correct ResourceIcon to the resource. Persist the resource is required
     * before firing this.
     *
     * @param AbstractResource $resource
     * @param string           $name (required if it's a file)
     * @param boolean          $isFixture (for testing purpose)
     *
     * @return AbstractResource
     */
    public function setResourceIcon(AbstractResource $resource, $mimeType = null, $isFixture = false)
    {
        $type = $resource->getResourceType();

        if ($type->getName() !== 'file') {
            $icon = $this->getTypeIcon($type);
        } else {
            $icon = $this->getFileIcon($resource, $mimeType, $isFixture);
        }

        $resource->setIcon($icon);

        return $resource;
    }

    /**
     * Create (if possible) and returns an icon for a file.
     *
     * @param AbstractResource $resource
     * @param string $mimeType
     * @param boolean $isFixture
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     *
     * @throws \InvalidArgumentException
     */
    public function getFileIcon(AbstractResource $resource, $mimeType, $isFixture)
    {
        if ($mimeType === null) {
            throw new \InvalidArgumentException("No mimeType specified for the file icon : {$resource->getPathForDisplay()}");
        }

        $mimeElements = explode('/', $mimeType);

        // if video or img => generate the thumbnail, otherwise find an existing one.
        if (($mimeElements[0] === 'video' || $mimeElements[0] === 'image') && $isFixture == false) {
//               throw new \Exception('gogogo');
            $originalPath = $this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $resource->getHashName();
            $newPath = $this->container->getParameter('claroline.thumbnails.directory') . DIRECTORY_SEPARATOR . $this->container->get('claroline.resource.utilities')->generateGuid().".png";
            $thumbnailPath = $this->createThumbNail($originalPath, $newPath, 100, 100, $mimeElements[1], $mimeElements[0]);

            if ($thumbnailPath !== null) {
                $thumbnailName = pathinfo($thumbnailPath, PATHINFO_BASENAME);
                $iconName = "thumbnails/{$thumbnailName}";
                $icon = new ResourceIcon();
                $generatedIconType = $this->em
                    ->getRepository('Claroline\CoreBundle\Entity\Resource\IconType')
                    ->find(IconType::GENERATED);
                $icon->setIconType($generatedIconType);
                $icon->setIconLocation($newPath);
                $icon->setRelativeUrl($iconName);
                $icon->setType('generated');
                $icon->setShortcut(false);
                $this->createShortcutIcon($icon);
                $this->em->persist($icon);
                $this->em->flush();

                return $icon;
            }
        }

        return $this->searchFileIcon($mimeType);
    }

    /**
     * Returns the icon for the specified ResourceType.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return  @return  \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function getTypeIcon(ResourceType $type)
    {
        $repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon = $repo->findOneBy(array('type' => $type->getName(), 'iconType' => IconType::TYPE));

        if ($icon === null) {
            $icon = $repo->findOneBy(array('type' => 'default', 'iconType' => IconType::DEFAULT_ICON));
        }

        return $icon;
    }

    /**
     * Return the icon of a specified mimeType.
     * The most specific icon for the mime type will be returned.
     *
     * @param string $mimeType
     *
     * @return  \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function searchFileIcon($mimeType)
    {
        $mimeElements = explode('/', $mimeType);
        $repo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon');

        $icon = $repo->findOneBy(array('type' => $mimeType, 'iconType' => IconType::COMPLETE_MIME_TYPE));

        if ($icon === null) {
            $icon = $repo->findOneBy(array('type' => $mimeElements[0], 'iconType' => IconType::BASIC_MIME_TYPE));

            if ($icon === null) {
                $icon = $repo->findOneBy(array('type' => 'file', 'iconType' => IconType::TYPE));
            }
        }

        return $icon;
    }

    /**
     * Creates a short cut Icon for an existing icon.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceIcon $icon
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     *
     * @throws \RuntimeException
     */
    public function createShortcutIcon(ResourceIcon $icon)
    {
        $ds = DIRECTORY_SEPARATOR;
        $basepath = $icon->getIconLocation();
        $extension = pathinfo($icon->getIconLocation(), PATHINFO_EXTENSION);
        $stampPath = "{$this->container->getParameter('kernel.root_dir')}{$ds}..{$ds}web{$ds}bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-black.png";

        if (function_exists($funcname = "imagecreatefrom{$extension}")) {
            $im = $funcname($basepath);
        } else {
            throw new \RuntimeException("Couldn't create a image from {$basepath}");
        }
        $stamp = imagecreatefrompng($stampPath);
        imagesavealpha($im, true);
        imagecopy($im, $stamp, 0, imagesy($im) - imagesy($stamp), 0, 0, imagesx($stamp), imagesy($stamp));
        $name = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        imagepng($im, $this->container->getParameter('claroline.thumbnails.directory').$ds.$name);
        imagedestroy($im);

        $shortcutIcon = new ResourceIcon();
        $shortcutIcon->setIconLocation("{$this->container->getParameter('claroline.thumbnails.directory')}{$ds}{$name}");
        $shortcutIcon->setRelativeUrl("thumbnails{$ds}{$name}");
        $shortcutIcon->setIconType($icon->getIconType());
        $shortcutIcon->setType($icon->getType());
        $shortcutIcon->setShortcut(true);
        $icon->setShortcutIcon($shortcutIcon);
        $shortcutIcon->setShortcutIcon($shortcutIcon);
        $this->em->persist($icon);
        $this->em->persist($shortcutIcon);
        $this->em->flush();

        return $shortcutIcon;
    }

    /**
     * Creates a custom icon entity from a File (wich should contain an image).
     *
     * @param UploadedFile $file
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function createCustomIcon(UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $iconName = $file->getClientOriginalName();;
        $extension = pathinfo($iconName, PATHINFO_EXTENSION);
        $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        $file->move($this->container->getParameter('claroline.thumbnails.directory'), $hashName);
        //entity creation
        $icon = new ResourceIcon();
        $icon->setIconLocation("{$this->container->getParameter('claroline.thumbnails.directory')}{$ds}{$hashName}");
        $icon->setRelativeUrl("thumbnails{$ds}{$hashName}");
        $icon->setIconType($this->em->getRepository('Claroline\CoreBundle\Entity\Resource\IconType')->find(IconType::CUSTOM_ICON));
        $icon->setType('custom');
        $icon->setShortcut(false);
        $this->em->persist($icon);
        $this->createShortcutIcon($icon);

        return $icon;
    }
}