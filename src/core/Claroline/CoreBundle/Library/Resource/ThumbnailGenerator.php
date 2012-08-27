<?php

namespace Claroline\CoreBundle\Library\Resource;

class ThumbnailGenerator
{

    /** @var string */ private $dir;

    /** @var bool */ private $hasGdExtension;

    /** @var bool */ private $hasFfmpegExtension;

    public function __construct($dir)
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
    }

    //the end could be refactored: what does imagedestroy should do ? is everything clean ?
    public function createThumbNail($name, $destinationPath, $newWidth, $newHeight)
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

}