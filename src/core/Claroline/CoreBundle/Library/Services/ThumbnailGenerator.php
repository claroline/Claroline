<?php

namespace Claroline\CoreBundle\Library\Services;

class ThumbnailGenerator
{
    /** @var string */
    private $dir;
    
    const WIDTH = 1;
    const HEIGHT = 1;
    
    public function __construct ($dir)
    {
        $this->dir = $dir;
    }
    
    public function createThumb($name, $filename, $new_w, $new_h)
    {
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        
        switch($extension)
        {
            case "jpeg":
                $src_img = imagecreatefromjpeg($name);
                break;
            case "jpg":
                var_dump("je passe");
                $src_img = imagecreatefromjpeg($name);
                break;
            case "pnj":    
                $src_img = imagecreatefrompng($name);
                break;
            default:
                return null;
        }
        
        $old_x=imageSX($src_img);
        $old_y=imageSY($src_img);
        
        if ($old_x > $old_y) 
        {
            $thumb_w=$new_w;
            $thumb_h=$old_y*($new_h/$old_x);
        }
        if ($old_x < $old_y) 
        {
            $thumb_w=$old_x*($new_w/$old_y);
            $thumb_h=$new_h;
        }
        if ($old_x == $old_y)
        {
            $thumb_w=$new_w;
            $thumb_h=$new_h;
        }
        
        $dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
        imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y);
        
        switch($extension)
        {
            case "jpeg":
                return $src_img = imagejpeg($dst_img,$filename); 
                break;
            case "jpg":
                return $src_img = imagejpeg($dst_img,$filename); 
                break;
            case "pnj":    
                return $src_img = imagepng($dst_img,$filename);
                break;
        }
        
        imagedestroy($dst_img); 
        imagedestroy($src_img);     
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