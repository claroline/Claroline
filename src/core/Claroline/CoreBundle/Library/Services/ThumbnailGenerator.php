<?php

namespace Claroline\CoreBundle\Library\Services;

class ThumbnailGenerator
{
    /** @var string */
    private $dir;
    
    const WIDTH = 50;
    const HEIGHT = 50;
    
    public function __construct ($dir)
    {
        $this->dir = $dir;
    }
    
    public function createThumb($name, $filename, $newWidth, $newHeight)
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        
        switch($extension)
        {
            case "jpeg":
                $srcImg = imagecreatefromjpeg($name);
                break;
            case "jpg":
                $srcImg = imagecreatefromjpeg($name);
                break;
            case "pnj":    
                $srcImg = imagecreatefrompng($name);
                break;
            default:
                return null;
        }
        
        $oldX = imagesx($srcImg);
        $oldY = imagesy($srcImg);
        
        if ($oldX > $oldY) 
        {
            $thumbWidth = $newWidth;
            $thumbHeight = $oldY*($newHeight/$oldX);
        }
        if ($oldX < $oldY) 
        {
            $thumbWidth = $oldX*($newWidth/$oldY);
            $thumbHeight = $newHeight;
        }
        if ($oldX == $oldY)
        {
            $thumbWidth = $newWidth;
            $thumbHeight = $newHeight;
        }
        
        $dstImg = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $oldX, $oldY);
        
        switch($extension)
        {
            case "jpeg":
                return $srcImg = imagejpeg($dstImg, $filename); 
                break;
            case "jpg":
                return $srcImg = imagejpeg($dstImg, $filename); 
                break;
            case "pnj":    
                return $srcImg = imagepng($dstImg, $filename);
                break;
        }
        
        imagedestroy($dstImg); 
        imagedestroy($srcImg);     
    }
    
    public function parseAllAndGenerate()
    {
        $iterator = new \DirectoryIterator($this->dir);
        $i=0;
        
        foreach($iterator as $fileInfo)
        {
            if($fileInfo->isFile())
            {
                $pathName = $fileInfo->getPathname();
                $path = $fileInfo->getPath();
                $fileName = $fileInfo->getFileName();
                $this->createThumb("{$pathName}", "{$path}/tn_{$fileName}", self::WIDTH, self::HEIGHT);
                
                var_dump($i);
                $i++;
                
            }
        }
    }
   
}