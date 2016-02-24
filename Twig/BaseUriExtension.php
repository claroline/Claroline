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

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class BaseUriExtension extends \Twig_Extension
{
    private $assetsHelper;

    /**
     * @DI\InjectParams({
     *     "helper" = @DI\Inject("templating.helper.assets")
     * })
     */
    public function __construct(AssetsHelper $helper)
    {
        $this->assetsHelper = $helper;
    }

    public function getFunctions()
    {
        return ['getAssetPath' => new \Twig_Function_Method($this, 'getAssetPath')];
    }

    public function getName()
    {
        return 'base_uri_extension';
    }

    /**
     * Returns the URI under which assets are served, without any trailing slash.
     *
     * @return string
     */
    public function getAssetPath()
    {
        $path = $this->assetsHelper->getUrl('');

        if ($path[strlen($path) - 1] === '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }
}
