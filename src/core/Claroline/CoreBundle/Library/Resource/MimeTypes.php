<?php

namespace Claroline\CoreBundle\Library\Resource;

class MimeTypes
{
    private static $mimeTypes = array(
        'mp4' => 'video/mp4',
        'mov' => 'video/mov',
        'flv' => 'video/flv',
        'ogg' => 'audio/ogg',
        'zip' => 'application/zip',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'jpg' => 'image/jpg',
        'jpeg'=> 'image/jpeg',
        'txt' => 'text/plain',
    );

    public static function getMimeType($extension)
    {
        return (array_key_exists($extension, self::$mimeTypes)) ? self::$mimeTypes[$extension] : 'claroline/default';
    }
}