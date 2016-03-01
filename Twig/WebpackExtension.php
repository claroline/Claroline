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
use Symfony\Bridge\Twig\Extension\AssetExtension;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class WebpackExtension extends \Twig_Extension
{
    private $assetExtension;
    private $environment;

    /**
     * @DI\InjectParams({
     *     "extension"      = @DI\Inject("twig.extension.assets"),
     *     "environment"    = @DI\Inject("%kernel.environment%")
     * })
     *
     * @param AssetExtension $extension
     */
    public function __construct(AssetExtension $extension, $environment)
    {
        $this->assetExtension = $extension;
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return [
            'hotAsset' => new \Twig_Function_Method($this, 'hotAsset')
        ];
    }

    public function getName()
    {
        return 'webpack_extension';
    }

    public function hotAsset($path)
    {
        if ($this->environment === 'dev') {
            return "http://localhost:8080/{$path}";
        }

        return $this->assetExtension->getAssetUrl($path);
    }
}
