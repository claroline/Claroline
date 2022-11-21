<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\Theme;
use Claroline\ThemeBundle\Repository\ThemeRepository;

class ThemeManager
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;

    /** @var ThemeRepository */
    private $repository;

    /** @var Theme */
    private $currentTheme;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config
    ) {
        $this->config = $config;
        $this->om = $om;

        $this->repository = $om->getRepository(Theme::class);
    }

    public function createTheme(string $themeName): Theme
    {
        $theme = new Theme();
        $theme->setName($themeName);

        $this->om->persist($theme);
        $this->om->flush();

        return $theme;
    }

    /**
     * Returns the names of all the registered themes.
     *
     * @return string[]
     */
    public function getAvailableThemes(): array
    {
        $themes = $this->all(true);
        $themeNames = [];

        foreach ($themes as $theme) {
            $themeNames[$theme->getNormalizedName()] = $theme->getName();
        }

        return $themeNames;
    }

    /**
     * Returns the current platform theme.
     *
     * NB. This method is called many times
     *     in the platform execution (find theme assets, locate custom templates, etc).
     *     So we cache the current theme in the service to avoid many DB calls.
     */
    public function getCurrentTheme(): Theme
    {
        if (empty($this->currentTheme)) {
            $this->currentTheme = $this->getThemeByNormalizedName(
                $this->config->getParameter('theme')
            );
        }

        if (empty($this->currentTheme)) {
            $this->currentTheme = $this->getDefaultTheme();
        }

        return $this->currentTheme;
    }

    /**
     * Lists all themes installed in the current platform.
     *
     * @return Theme[]
     */
    public function all(?bool $onlyEnabled = false)
    {
        return $this->repository->findAll($onlyEnabled);
    }

    /**
     * Returns the default platform theme.
     */
    public function getDefaultTheme(): Theme
    {
        $default = $this->repository->findOneBy(['default' => true]);

        if (!$default) {
            $default = $this->repository->findAll()[0];
        }

        return $default;
    }

    /**
     * Finds a theme by its name.
     */
    public function getThemeByName(string $name): ?Theme
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    /**
     * Finds a theme by its normalized name.
     */
    private function getThemeByNormalizedName(string $normalizedName): ?Theme
    {
        return $this->getThemeByName(
            ucwords(str_replace('-', ' ', $normalizedName))
        );
    }
}
