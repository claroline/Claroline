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

use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Manager\ThemeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\Extension\AssetExtension;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class ThemeExtension extends \Twig_Extension
{
    /**
     * @var
     */
    private $assetExtension;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var Theme
     */
    private $currentTheme;

    /**
     * ThemeExtension constructor.
     *
     * @DI\InjectParams({
     *     "extension"      = @DI\Inject("twig.extension.assets"),
     *     "themeManager" = @DI\Inject("claroline.manager.theme_manager")
     * })
     *
     * @param AssetExtension $extension
     * @param ThemeManager   $themeManager
     */
    public function __construct(
        AssetExtension $extension,
        ThemeManager $themeManager)
    {
        $this->assetExtension = $extension;
        $this->themeManager = $themeManager;
    }

    public function getName()
    {
        return 'theme_extension';
    }

    public function getFunctions()
    {
        return [
            'themeAsset' => new \Twig_Function_Method($this, 'themeAsset'),
        ];
    }

    public function themeAsset($path)
    {
        if (!$this->currentTheme) {
            // Retrieve current theme
            $this->currentTheme = $this->themeManager->getCurrentTheme();
        }

        $assetName = pathinfo($path, PATHINFO_FILENAME);
        $assetPath = 'themes/'.$this->currentTheme->getNormalizedName().'/'.trim($path, '/\\');

        return $this->assetExtension->getAssetUrl($assetPath.'?v=');
    }
}
