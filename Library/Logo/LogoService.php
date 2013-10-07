<?php

namespace Claroline\CoreBundle\Library\Logo;

use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Service("claroline.common.logo_service")
 */
class LogoService
{
    private $container;
    private $path;
    private $fileTypes;
    private $finder;

    public function __construct()
    {
        $this->path = __DIR__."/../../../../../../../web/uploads/logos/";
        $this->fileTypes = '/\.jpg$|\.png$|\.gif$|\.jpeg$/';
        $this->finder = new Finder();
    }

    public function listLogos()
    {
        $logos = array();
        $files = $this->finder->files()->in($this->path)->name($this->fileTypes);

        foreach ($files as $file) {
            $logos[] = $file->getRelativePathname();
        }

        return $logos;
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function createLogo(UploadedFile $file)
    {
        if ($file->getMimeType() and strpos($file->getMimeType(), 'image/') === 0) {
            $file->move($this->path, uniqid().'.'.$file->guessExtension());
        }
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteLogo($file)
    {
        if (file_exists($this->path.$file)) {
            unlink($this->path.$file);
        }
    }
}
