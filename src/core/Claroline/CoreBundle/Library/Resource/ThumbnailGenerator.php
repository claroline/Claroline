<?php

namespace Claroline\CoreBundle\Library\Resource;

class ThumbnailGenerator
{
    /** @var string */
    private $dir;

    /** @var bool */
    private $hasGdExtension;

    /** @var bool */
    private $hasFfmpegExtension;

    const WIDTH = 50;
    const HEIGHT = 50;

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
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        if ($this->hasGdExtension) {
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

            return $destinationPath;

        } else {
            //something went wrong.
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
            if ($oldX < $oldY) {
                $thumbWidth = $oldX * ($newWidth / $oldY);
                $thumbHeight = $newHeight;
            }
        }

        $dstImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $oldX, $oldY);

        return $srcImg = imagepng($dstImg, $filename);
    }

    public function parseAllAndGenerate()
    {
        $iterator = new \DirectoryIterator($this->dir);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $pathName = $fileInfo->getPathname();
                $path = $fileInfo->getPath();
                $fileName = $fileInfo->getFileName();
                $this->createThumbNail("{$pathName}", "{$path}/thumbs/tn@{$fileName}", self::WIDTH, self::HEIGHT);
            }
        }
    }

    private function createMpegGDI($name)
    {
        if ($this->hasFfmpegExtension) {
            $media = new \ffmpeg_movie($name);
            $frameCount = $media->getFrameCount();
            $frame = $media->getFrame(round($frameCount / 2));
            $gdImage = $frame->toGDImage();

            return $gdImage;
        } else {
            //something went wrong
            return null;
        }
    }
}