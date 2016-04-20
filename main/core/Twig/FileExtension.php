<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;

/**
 * @Service
 * @Tag("twig.extension")
 */
class FileExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'is_writable' => new \Twig_Function_Method($this, 'isWritable'),
        );
    }

    public function getName()
    {
        return 'file_extension';
    }

    public function isWritable($path)
    {
        return is_writable($path);
    }
}
