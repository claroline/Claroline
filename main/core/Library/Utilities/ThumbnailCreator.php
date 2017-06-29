<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use JangoBrick\SVG\Nodes\Embedded\SVGImageElement;
use JangoBrick\SVG\SVGImage;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @DI\Service("claroline.utilities.thumbnail_creator")
 */
class ThumbnailCreator
{
    private $webDir;
    private $thumbnailDir;
    private $isGdLoaded;
    private $isFfmpegLoaded;
    private $ut;
    private $fs;
    private $fileUtilities;

    /**
     * @DI\InjectParams({
     *     "kernelRootDir"      = @DI\Inject("%kernel.root_dir%"),
     *     "thumbnailDirectory" = @DI\Inject("%claroline.param.thumbnails_directory%"),
     *     "ut"                 = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct($kernelRootDir, $thumbnailDirectory, ClaroUtilities $ut)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->webDir = "{$kernelRootDir}{$ds}..{$ds}web";
        $this->thumbnailDir = $thumbnailDirectory;
        $this->isGdLoaded = extension_loaded('gd');
        $this->isFfmpegLoaded = extension_loaded('ffmpeg');
        $this->ut = $ut;
        $this->fs = new FileSystem();
    }

    /**
     * Create an thumbnail from a video. Returns null if the creation failed.
     *
     * @param string $originalPath    the path of the orignal video
     * @param string $destinationPath the path were the thumbnail will be copied
     * @param int    $newWidth        the width of the thumbnail
     * @param int    $newHeight       the width of the thumbnail
     *
     * @return string
     */
    public function fromVideo($originalPath, $destinationPath, $newWidth, $newHeight)
    {
        if (!$this->isGdLoaded || !$this->isFfmpegLoaded) {
            $message = '';
            if (!$this->isGdLoaded) {
                $message .= 'The GD extension is missing \n';
            }
            if (!$this->isFfmpegLoaded) {
                $message .= 'The Ffmpeg extension is missing \n';
            }

            throw new UnloadedExtensionException($message);
        }

        $media = new \ffmpeg_movie($originalPath);
        $frameCount = $media->getFrameCount();
        $frame = $media->getFrame(round($frameCount / 2));

        if ($frame) {
            $image = $frame->toGDImage();
            $this->resize($newWidth, $newHeight, $image, $destinationPath);

            return $destinationPath;
        }

        $exception = new ExtensionNotSupportedException();
        $exception->setExtension(pathinfo($originalPath, PATHINFO_EXTENSION));
        throw $exception;
    }

    /**
     * Create an thumbnail from an image. Returns null if the creation failed.
     *
     * @param string $originalPath    the path of the orignal image
     * @param string $destinationPath the path were the thumbnail will be copied
     * @param int    $newWidth        the width of the thumbnail
     * @param int    $newHeight       the width of the thumbnail
     *
     * @return string
     */
    public function fromImage($originalPath, $destinationPath, $newWidth, $newHeight)
    {
        if (!$this->isGdLoaded) {
            throw new UnloadedExtensionException('The GD extension is missing \n');
        }

        if (file_exists($originalPath)) {
            /*
            This function is deprecated.
            I had to do this for the DnD upload (.jpg files have png mime for some reasons).
            It would be nice to know why it works this way.
            */
            $mime = mime_content_type($originalPath);
            $eMime = explode('/', $mime);
            $extension = $eMime[1];
        } else {
            throw new \Exception("The file {$originalPath} doesn't exists.");
        }

        if (function_exists($funcname = "imagecreatefrom{$extension}")) {
            $srcImg = $funcname($originalPath);
        } else {
            $exception = new ExtensionNotSupportedException();
            $exception->setExtension($extension);
            throw $exception;
        }

        $this->resize($newWidth, $newHeight, $srcImg, $destinationPath);
        imagedestroy($srcImg);

        return $destinationPath;
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

    //TODO REMOVE thumbnail directory
    public function shortcutThumbnail(
        $srcImg,
        $stampImg = null,
        $targetDirPath = null,
        $filename = null // Just the filename, no extension
)
    {
        if (!$this->isGdLoaded) {
            throw new UnloadedExtensionException('The GD extension is missing \n');
        }

        $ds = DIRECTORY_SEPARATOR;
        if (is_null($stampImg) || !$this->fs->exists($stampImg)) {
            $stampImg = "{$this->webDir}{$ds}".$this->getDefaultStampRelativeUrl();
        }
        // Get image and its extension
        list($im, $extension) = $this->getImageAndExtensionFromUrl($srcImg);
        // Get stamp and its extension
        list($stamp, $stampExtension) = $this->getImageAndExtensionFromUrl($stampImg);

        if (is_null($filename)) {
            $filename = "{$this->ut->generateGuid()}.{$extension}";
        } else {
            $filename .= ".{$extension}";
        }

        if (!empty($targetDirPath)) {
            $dir = $targetDirPath.$ds.$filename;
        } else {
            $dir = $this->thumbnailDir.$ds.$filename;
        }

        if ($extension === 'svg') {
            if ($stampExtension === 'svg') {
                // Add all elements of $stamp to $im
                $stampDocument = $stamp->getDocument();
                $imDocument = $im->getDocument();
                $stampDocument->setHeight($imDocument->getHeight());
                $stampDocument->setWidth($imDocument->getWidth());
                $shortcut = new SVGImage($imDocument->getWidth(), $imDocument->getHeight());
                $shortcut->getDocument()->addChild($imDocument);
                $shortcut->getDocument()->addChild($stampDocument);
                $im = $shortcut;
            } else {
                $im->getDocument()->addChild(new SVGImageElement(
                    'data:'.mime_content_type($stampImg).';base64,'.base64_encode(file_get_contents($stampImg)),
                    0,
                    $im->getDocument()->getHeight() - imagesy($stamp),
                    imagesx($stamp),
                    imagesy($stamp)
                ));
            }
            $this->fs->dumpFile($dir, $im);
        } else {
            if ($stampExtension === 'svg') {
                $stamp = $stamp->toRasterImage(imagesx($im), imagesy($im));
            }
            imagecopy($im, $stamp, 0, imagesy($im) - imagesy($stamp), 0, 0, imagesx($stamp), imagesy($stamp));
            $funcname = "image{$extension}";
            $funcname($im, $dir);
            imagedestroy($im);
            imagedestroy($stamp);
        }

        return $dir;
    }

    public function getDefaultStampRelativeUrl()
    {
        $ds = DIRECTORY_SEPARATOR;

        return "bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-black.png";
    }

    private function getImageAndExtensionFromUrl($url)
    {
        if (!file_exists($url)) {
            throw new FileNotFoundException("File not found: '${url}'");
        }
        $imageType = exif_imagetype($url);
        $imageContent = file_get_contents($url);
        // Check if imagetype is false or if image is svg
        if (!$imageType) {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            if ($extension === 'svg' || (preg_match('/^<\?xml/', $imageContent) && strpos($imageContent, '<svg') !== false)) {
                $image = SVGImage::fromFile($url);

                return [$image, 'svg'];
            }
            $exception = new ExtensionNotSupportedException();
            $exception->setExtension($extension);

            throw $exception;
        }
        // Let php find about extension as sometimes files has no extension or have a fake extension
        $extension = str_replace('.', '', image_type_to_extension($imageType));

        if (!function_exists("image{$extension}")) {
            $exception = new ExtensionNotSupportedException();
            $exception->setExtension($extension);

            throw $exception;
        }

        try {
            $image = imagecreatefromstring($imageContent);
        } catch (\Exception $e) {
            $exception = new ExtensionNotSupportedException($e->getMessage());
            $exception->setExtension($extension);

            throw $exception;
        }

        return [$image, $extension];
    }
}
