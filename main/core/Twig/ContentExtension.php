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

class ContentExtension extends \Twig_Extension
{
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getFunctions()
    {
        return [
            'getContent' => new \Twig_SimpleFunction('getContent', [$this, 'getContent']),
        ];
    }

    public function getName()
    {
        return 'content_extension';
    }

    public function getContent($type)
    {
        return $this->content->getContent(['type' => $type]);
    }
}
