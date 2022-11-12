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
    /** @var ThemeRepository */
    private $repository;
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var Theme */
    private $currentTheme;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config
    ) {
        $this->repository = $om->getRepository(Theme::class);
        $this->config = $config;
    }

    /**
     * Returns the names of all the registered themes.
     *
     * @return string[]
     */
    public function listThemeNames(?bool $onlyEnabled = false): array
    {
        $themes = $this->all($onlyEnabled);
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
