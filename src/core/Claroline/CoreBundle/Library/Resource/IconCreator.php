<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File as ResourceFile;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

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
     * Create an thumbnail from a file (video or image) wich will be resized and displayed as a resource icon.
     *
     * @param string  $originalPath    the path of the orignal image or video
     * @param string  $destinationPath the path were the thumbnail will be copied
     * @param integer $newWidth        the width of the thumbnail
     * @param integer $newHeight       the width of the thumbnail
     *
     * @return string|null
     */
    private function createThumbNail($originalPath, $destinationPath, $newWidth, $newHeight)
    {
        $mimeElements = explode('/', MimeTypeGuesser::getInstance()->guess($originalPath));
        $baseMime = $mimeElements[0];
        $mimeExtension = $mimeElements[1];

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

            $this->resize($newWidth, $newHeight, $srcImg, $destinationPath);
            imagedestroy($srcImg);

            return $destinationPath;
        }

        return null;
    }

    /**
     * Create a copy of a resized image according to the parameters.
     *
     * @param string $newWidth  the new width
     * @param string $newHeight the new heigth
     * @param string $srcImg    the path of the source
     * @param string $filename  the path of the copy
     */
    private function resize($newWidth, $newHeight, $srcImg, $filename)
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
     * @param boolean          $isFixture (for testing purpose)
     *
     * @return AbstractResource
     */
    public function setResourceIcon(AbstractResource $resource, $isFixture = false)
    {
        $type = $resource->getResourceType();

        if ($type->getName() !== 'file') {
            $icon = $this->getTypeIcon($type);
        } else {
            if ($resource->getMimeType() === null) {
                throw new \RuntimeException("The entity {$resource->getName()} as no mime type set");
            }
            $icon = $this->getFileIcon($resource, $isFixture);
        }

        $resource->setIcon($icon);

        return $resource;
    }

    /**
     * Create (if possible) and returns an icon for a file.
     *
     * @param File    $resource  the file
     * @param boolean $isFixture
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     *
     * @throws \InvalidArgumentException
     */
    public function getFileIcon(ResourceFile $resource, $isFixture)
    {
        $mimeElements = explode('/', $resource->getMimeType());

        // if video or img => generate the thumbnail, otherwise find an existing one.
        if (($mimeElements[0] === 'video' || $mimeElements[0] === 'image') && $isFixture == false) {
            $originalPath = $this->container->getParameter('claroline.files.directory')
                . DIRECTORY_SEPARATOR . $resource->getHashName();
            $newPath = $this->container->getParameter('claroline.thumbnails.directory')
                . DIRECTORY_SEPARATOR
                . $this->container->get('claroline.resource.utilities')->generateGuid() . ".png";
            $thumbnailPath = $this->createThumbNail($originalPath, $newPath, 100, 100);

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

        return $this->searchFileIcon($resource->getMimeType());
    }

    /**
     * Returns the icon for the specified ResourceType.
     *
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceType $type
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon the resource type
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
     * Creates the shortcut icon for an existing icon.
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
        $stampPath = "{$this->container->getParameter('kernel.root_dir')}{$ds}..{$ds}web{$ds}bundles{$ds}"
            . "clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-black.png";

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
        $location = "{$this->container->getParameter('claroline.thumbnails.directory')}{$ds}{$name}";
        $shortcutIcon->setIconLocation($location);
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
     * Creates a custom ResourceIcon entity from a File (wich should contain an image).
     * (for instance if the thumbnail of a resource is changed)
     *
     * @param UploadedFile $file
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceIcon
     */
    public function createCustomIcon(UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $iconName = $file->getClientOriginalName();
        $extension = pathinfo($iconName, PATHINFO_EXTENSION);
        $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        $file->move($this->container->getParameter('claroline.thumbnails.directory'), $hashName);
        //entity creation
        $icon = new ResourceIcon();
        $icon->setIconLocation("{$this->container->getParameter('claroline.thumbnails.directory')}{$ds}{$hashName}");
        $icon->setRelativeUrl("thumbnails{$ds}{$hashName}");
        $customType = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\IconType')
            ->find(IconType::CUSTOM_ICON);
        $icon->setIconType($customType);
        $icon->setType('custom');
        $icon->setShortcut(false);
        $this->em->persist($icon);
        $this->createShortcutIcon($icon);

        return $icon;
    }
}