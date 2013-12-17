<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Lang;

use Symfony\Component\Finder\Finder;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("claroline.common.lang_service")
 */
class LangService
{
    private $finder;
    private $path;

    public function __construct($path = '/../../Resources/translations/')
    {
        $this->finder = new Finder();
        $this->path = $path;
    }

    /**
     * Get a list of available languages in the platform.
     */
    public function getLangs()
    {
        $langs = array();

        $finder = $this->finder->files()->in(__DIR__.$this->path)->name('/platform\.[^.]*\.yml/');
        foreach ($finder as $file) {
            $lang = str_replace(array('platform.', '.yml'), '', $file->getRelativePathname());
            $langs[$lang] = $lang;
        }

        return $langs;
    }

}
