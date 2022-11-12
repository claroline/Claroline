<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Twig;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Manager\ThemeManager;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ThemeExtension extends AbstractExtension
{
    /** @var AssetExtension */
    private $assetExtension;
    /** @var ThemeManager */
    private $themeManager;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var string */
    private $projectDir;
    /** @var array */
    private $assetCache;

    public function __construct(
        AssetExtension $extension,
        ThemeManager $themeManager,
        PlatformConfigurationHandler $config,
        string $projectDir
    ) {
        $this->assetExtension = $extension;
        $this->themeManager = $themeManager;
        $this->config = $config;
        $this->projectDir = $projectDir;
    }

    public function getName(): string
    {
        return 'theme_extension';
    }

    public function getFunctions(): array
    {
        return [
            'themeAsset' => new TwigFunction('themeAsset', [$this, 'themeAsset']),
        ];
    }

    public function themeAsset($path): string
    {
        $themeName = $this->config->getParameter('theme');
        $themeName = str_replace(' ', '-', strtolower($themeName));
        $assets = $this->getThemeAssets();

        if (!isset($assets[$themeName]) || !isset($assets[$themeName][$path])) {
            // selected theme can not be found, fall back to default theme
            $defaultTheme = $this->themeManager->getDefaultTheme();
            $themeName = $defaultTheme->getNormalizedName();

            if (!isset($assets[$themeName]) || !isset($assets[$themeName][$path])) {
                // default theme not found too, this time we can not do anything
                $assetNames = implode("\n", array_keys($assets));

                throw new \Exception("Cannot find asset '{$path}' for theme '{$themeName}' "."in theme build. Found:\n{$assetNames})");
            }
        }

        return $this->assetExtension->getAssetUrl(
            'themes/'.$themeName.'/'.$path.'?v='.$assets[$themeName][$path]
        );
    }

    private function getThemeAssets(): array
    {
        if (!$this->assetCache) {
            $assetFile = "{$this->projectDir}/theme-assets.json";

            if (!file_exists($assetFile)) {
                throw new \Exception(sprintf('Cannot find theme generated assets file(s). Make sure you have built them.'));
            }

            $this->assetCache = json_decode(file_get_contents($assetFile), true);
        }

        return $this->assetCache;
    }
}
