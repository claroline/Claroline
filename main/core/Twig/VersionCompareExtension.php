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
class VersionCompareExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'version_compare' => new \Twig_Function_Method($this, 'versionCompare'),
        );
    }

    public function getName()
    {
        return 'version_compare_extension';
    }

    public function versionCompare($old, $new, $operator)
    {
        return version_compare($old, $new, $operator);
    }
}
