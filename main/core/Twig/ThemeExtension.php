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

    private $rootDir;
    private $assetCache;

    /**
     * ThemeExtension constructor.
     *
     * @DI\InjectParams({
     *     "extension"    = @DI\Inject("twig.extension.assets"),
     *     "themeManager" = @DI\Inject("claroline.manager.theme_manager"),
     *     "rootDir"      = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param AssetExtension $extension
     * @param ThemeManager   $themeManager
     * @param string         $rootDir
     */
    public function __construct(
        AssetExtension $extension,
        ThemeManager $themeManager,
        $rootDir)
    {
        $this->assetExtension = $extension;
        $this->themeManager = $themeManager;
        $this->rootDir = $rootDir;
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

    public function themeAsset($path, $themeName = null)
    {
        if (empty($themeName)) {
            if (!$this->currentTheme) {
                // Retrieve current theme
                $this->currentTheme = $this->themeManager->getCurrentTheme();
            }

            $themeName = $this->currentTheme->getNormalizedName();
        }

        $assets = $this->getThemeAssets();

        if (!isset($assets[$themeName]) || !isset($assets[$themeName][$path])) {
            $assetNames = implode("\n", array_keys($assets));

            throw new \Exception(
                "Cannot find asset '{$path}' for theme '{$themeName}' ".
                "in theme build. Found:\n{$assetNames})"
            );
        }

        return $this->assetExtension->getAssetUrl(
            'themes/'.$themeName.'/'.$path.'?v='.$assets[$themeName][$path]
        );
    }

    private function getThemeAssets()
    {
        if (!$this->assetCache) {
            $assetFile = "{$this->rootDir}/../theme-assets.json";

            if (!file_exists($assetFile)) {
                throw new \Exception(sprintf(
                    'Cannot find theme generated assets file(s). Make sure you have built them.'
                ));
            }

            $this->assetCache = json_decode(file_get_contents($assetFile), true);
        }

        return $this->assetCache;
    }
}
