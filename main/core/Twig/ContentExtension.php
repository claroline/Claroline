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

/**
 * @Service
 * @Tag("twig.extension")
 */
class ContentExtension extends \Twig_Extension
{
    protected $content;

    /**
     * @InjectParams({
     *     "content" = @Inject("claroline.manager.content_manager")
     * })
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getFunctions()
    {
        return array(
            'getContent' => new \Twig_Function_Method($this, 'getContent'),
        );
    }

    public function getName()
    {
        return 'content_extension';
    }

    public function getContent($type)
    {
        return $this->content->getContent(array('type' => $type));
    }
}
