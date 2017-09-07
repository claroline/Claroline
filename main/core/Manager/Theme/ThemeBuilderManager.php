<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Theme;

use Claroline\CoreBundle\Entity\Theme\Theme;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Manages compilation of the platform's themes.
 *
 * @DI\Service("claroline.manager.theme_builder")
 */
class ThemeBuilderManager
{
    /**
     * Path for theme installed through core and plugins.
     * NB: the path is prefixed with the bundle path.
     *
     * @var string
     */
    const INSTALLED_THEME_PATH = 'Resources'.DIRECTORY_SEPARATOR.'themes';

    /**
     * Path for theme created by users in the current platform.
     * NB: the path is prefixed with the platform files directory path.
     *
     * @var string
     */
    const CUSTOM_THEME_PATH = 'themes-src';

    /** @var KernelInterface */
    private $kernel;

    /**
     * User storage directory.
     *
     * @var string
     */
    private $filesDir;

    /**
     * Compiled file directory.
     *
     * @var string
     */
    private $buildDir;

    /**
     * BuildManager constructor.
     *
     * @DI\InjectParams({
     *     "kernel"    = @DI\Inject("kernel"),
     *     "filesDir"  = @DI\Inject("%claroline.param.files_directory%"),
     *     "kernelDir" = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param KernelInterface $kernel
     * @param string          $filesDir
     * @param string          $kernelDir
     */
    public function __construct(KernelInterface $kernel, $filesDir, $kernelDir)
    {
        $this->kernel = $kernel;
        $this->filesDir = $filesDir;
        $this->buildDir = implode(DIRECTORY_SEPARATOR, [$kernelDir, '..', 'web', 'themes']);
    }

    /**
     * Rebuilds the list of themes passed as argument.
     *
     * @param Theme[] $themes
     * @param bool    $cache
     *
     * @return array
     */
    public function rebuild(array $themes, $cache = true)
    {
        $logs = [];

        foreach ($themes as $theme) {
            $logs[$theme->getNormalizedName()] = $this->rebuildTheme($theme, $cache);
        }

        return $logs;
    }

    public function getBuildDir()
    {
        return $this->buildDir;
    }

    public function getThemeDir(Theme $theme)
    {
        $themeSrc = null;

        $plugin = $theme->getPlugin();
        if (!empty($plugin)) {
            // installed themes are located inside symfony bundles
            // load bundle instance from kernel if it's enabled
            try {
                $bundle = $this->kernel->getBundle($plugin->getSfName());
                $themeSrc = implode(DIRECTORY_SEPARATOR, [$bundle->getPath(), static::INSTALLED_THEME_PATH]);
            } catch (\InvalidArgumentException $e) {
                // the bundle is not enabled, just do nothing
            }
        } else {
            // custom themes are in the files directory of the platform
            $themeSrc = implode(DIRECTORY_SEPARATOR, [$this->filesDir, static::CUSTOM_THEME_PATH]);
        }

        if (!empty($themeSrc)) {
            $themeSrc .= DIRECTORY_SEPARATOR.$theme->getNormalizedName();
            // check expected source files exist
            if (!file_exists($themeSrc) && !file_exists($themeSrc.'.less')) {
                return null;
            }
        }

        return $themeSrc;
    }

    private function rebuildTheme(Theme $theme, $cache = true)
    {
        $logs = [];

        $themeSrc = $this->getThemeDir($theme);
        if (!empty($themeSrc)) {
            $logs[] = $this->compileTheme($themeSrc, $cache);
        } else {
            $logs[] = 'No source files found for theme';
        }

        return $logs;
    }

    private function compileTheme($themeSrc, $cache = true)
    {
        $compileCmd = sprintf('npm run themes -- --theme="%s"', $themeSrc);
        if (!$cache) {
            $compileCmd .= ' --no-cache';
        }

        exec($compileCmd, $out);

        return $out;
    }
}
