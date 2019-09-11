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

class RouterExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            'get_host' => new \Twig_SimpleFunction('get_host', [$this, 'getHost']),
        ];
    }

    public function getName()
    {
        return 'router_extension';
    }

    public function getHost()
    {
        return $this->container->get('request_stack')->getMasterRequest()->getSchemeAndHttpHost().
            $this->container->get('router')->getContext()->getBaseUrl();
    }
}
