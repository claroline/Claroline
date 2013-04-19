<?php

namespace Claroline\CoreBundle\Library\Utilities;

use Claroline\CoreBundle\Library\Resource\Utilities;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.utilities.thumbnail_creator")
 */
class ThumbnailCreator
{
    /**
     * @DI\InjectParams({
     *     "kernelRootDir" = @DI\Inject("%kernel.root_dir%"),
     *     "thumbnailDirectory" = @DI\Inject("%claroline.param.thumbnails_directory%"),
     *     "utilities" = @DI\Inject("claroline.resource.utilities")
     * })
     */
    public function __construct($kernelRootDir, $thumbnailDirectory, Utilities $utilities)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->webDir = "{$kernelRootDir}{$ds}..{$ds}web";
        $this->utilities = $utilities;
        $this->thumbnailDir = $thumbnailDirectory;
        $this->isGdLoaded = extension_loaded('gd');
        $this->isFfmpegLoaded = extension_loaded('ffmpeg');
    }

    /**
     * Create an thumbnail from a video. Returns null if the creation failed.
     *
     * @param string  $originalPath    the path of the orignal video
     * @param string  $destinationPath the path were the thumbnail will be copied
     * @param integer $newWidth        the width of the thumbnail
     * @param integer $newHeight       the width of the thumbnail
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
            if (!$this->isFfmegLoaded) {
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
     * @param string  $originalPath    the path of the orignal image
     * @param string  $destinationPath the path were the thumbnail will be copied
     * @param integer $newWidth        the width of the thumbnail
     * @param integer $newHeight       the width of the thumbnail
     *
     * @return string
     */
    public function fromImage($originalPath, $destinationPath, $newWidth, $newHeight)
    {
        if (!$this->isGdLoaded) {
             throw new UnloadedExtensionException('The GD extension is missing \n');
        }

        $extension = (pathinfo($originalPath, PATHINFO_EXTENSION) == 'jpg') ?
            'jpeg':
            pathinfo($originalPath, PATHINFO_EXTENSION);

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

    public function shortcutThumbnail($srcImg)
    {
        if (!$this->isGdLoaded) {
             throw new UnloadedExtensionException('The GD extension is missing \n');
        }

        $ds = DIRECTORY_SEPARATOR;
        $stampPath = "{$this->webDir}{$ds}bundles{$ds}"
            . "clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}shortcut-black.png";
        $extension = (pathinfo($srcImg, PATHINFO_EXTENSION) == 'jpg') ? 'jpeg': pathinfo($srcImg, PATHINFO_EXTENSION);
        if (function_exists($funcname = "imagecreatefrom{$extension}")) {
            $im = $funcname($srcImg);
        } else {
            $exception = new ExtensionNotSupportedException();
            $exception->setExtension($extension);
            throw $exception;
        }
        $stamp = imagecreatefrompng($stampPath);
        imagesavealpha($im, true);
        imagecopy($im, $stamp, 0, imagesy($im) - imagesy($stamp), 0, 0, imagesx($stamp), imagesy($stamp));
        $name = "{$this->utilities->generateGuid()}.{$extension}";
        imagepng($im, $this->thumbnailDir.$ds.$name);
        imagedestroy($im);

        return "{$this->thumbnailDir}{$ds}{$name}";
    }
}

