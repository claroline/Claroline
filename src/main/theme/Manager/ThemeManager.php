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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ThemeBundle\Entity\Theme;
use Claroline\ThemeBundle\Entity\UserPreferences;
use Claroline\ThemeBundle\Repository\ThemeRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ThemeManager
{
    private ThemeRepository $repository;
    private ?Theme $currentTheme = null;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly SerializerProvider $serializer,
        private readonly IconSetManager $iconManager,
    ) {
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
     * Computes UI appearance based on the current platform theme and user preferences.
     */
    public function getAppearance(): array
    {
        $theme = $this->getCurrentTheme();

        $userPreferences = null;
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if ($currentUser instanceof User) {
            $userPreferences = $this->om->getRepository(UserPreferences::class)->findOneBy(['user' => $currentUser]);
        }

        return array_merge([], $this->serializer->serialize($theme), [
            'icons' => $this->iconManager->getCurrentSet(),
        ], $userPreferences ? $this->serializer->serialize($userPreferences) : []);
    }

    /**
     * Returns the names of all the registered themes.
     *
     * @return string[]
     */
    public function getAvailableThemes(bool $onlyEnabled = true): array
    {
        $themes = $this->all($onlyEnabled);
        $themeNames = [];

        foreach ($themes as $theme) {
            $themeNames[] = $this->serializer->serialize($theme);
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
