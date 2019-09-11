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

class HomeExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            'isDesktop' => new \Twig_SimpleFunction('isDesktop', [$this, 'isDesktop']),
        ];
    }

    /**
     * Check if you come from desktop or workspace.
     */
    public function isDesktop()
    {
        if ($this->container->get('session')->get('isDesktop')) {
            return true;
        }

        return false;
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'home_extension';
    }
}
