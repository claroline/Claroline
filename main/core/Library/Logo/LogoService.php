<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Logo;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.common.logo_service")
 */
class LogoService
{
    private $path;
    private $fileTypes;
    private $finder;

    /**
     * @DI\InjectParams({
     *     "path" = @DI\Inject("%claroline.param.logos_directory%")
     * })
     */
    public function __construct($path)
    {
        $this->path = $path.'/';
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
