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

use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @DI\Service("claroline.common.logo_service")
 */
class LogoService
{
    private $path;
    private $fileTypes;
    private $finder;
    private $fu;
    private $filedir;

    const PUBLIC_FILE_TYPE = 'platform-logo';

    /**
     * @DI\InjectParams({
     *     "fu"      = @DI\Inject("claroline.utilities.file"),
     *     "path"    = @DI\Inject("%claroline.param.logos_directory%"),
     *     "filedir" = @DI\Inject("%claroline.param.files_directory%"),
     * })
     */
    public function __construct($path, FileUtilities $fu, $filedir)
    {
        $this->path = $path.'/';
        $this->fileTypes = '/\.jpg$|\.png$|\.gif$|\.jpeg$/';
        $this->finder = new Finder();
        $this->fu = $fu;
        $this->filedir = $filedir;
    }

    public function listLogos()
    {
        $logos = [];
        $files = $this->finder->files()->in($this->path)->name($this->fileTypes);

        foreach ($files as $file) {
            $logos[] = 'uploads/logos/'.$file->getRelativePathname();
        }

        $publicLogos = $this->fu->getPublicFileByType(self::PUBLIC_FILE_TYPE);

        foreach ($publicLogos as $publicLogo) {
            $logos[] = $publicLogo->getUrl();
        }

        return $logos;
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function createLogo(File $file)
    {
        $publicFile = $this->fu->createFile($file, $file->getBasename(), null, null, null, self::PUBLIC_FILE_TYPE);

        return $this->fu->createFileUse($publicFile, 'none', 'none');
    }

    /**
     * @Secure(roles="ROLE_ADMIN")
     */
    public function deleteLogo($file)
    {
        //old system
        $path = realpath($this->path.$file);

        //new public file system
        if (!$path) {
            $path = realpath($this->filedir.'/'.$file);
        }

        $publicFile = $this->fu->getOneBy(['url' => $file]);
        $this->fu->deletePublicFile($publicFile);

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
