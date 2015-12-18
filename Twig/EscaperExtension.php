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

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Service
 * @Tag("twig.extension")
 */
class EscaperExtension extends \Twig_Extension
{
    protected $content;


    public function getFilters()
    {
        return array(
            'ng_espace' => new \Twig_Filter_Method($this, 'ngEspace'),
        );
    }

    public function getName()
    {
        return 'espacer_extension';
    }

    public function ngEspace($content)
    {
        return str_replace('"', '\"', $content);
    }

}
