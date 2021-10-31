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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\ThemeBundle\Entity\Theme;
use Claroline\ThemeBundle\Repository\ThemeRepository;
use Claroline\ThemeBundle\Serializer\ThemeSerializer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ThemeManager
{
    /** @var ObjectManager */
    private $om;
    /** @var ThemeRepository */
    private $repository;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ThemeSerializer */
    private $serializer;
    /** @var string */
    private $themeDir;
    /** @var Theme */
    private $currentTheme;

    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $config,
        string $kernelDir,
        ThemeSerializer $serializer
    ) {
        $this->om = $om;
        $this->repository = $this->om->getRepository(Theme::class);
        $this->authorization = $authorization;
        $this->config = $config;
        $this->themeDir = $kernelDir.'/../public/themes';
        $this->serializer = $serializer;
    }

    /**
     * Serializes a ScheduledTask entity for the JSON api.
     *
     * @param Theme $theme - the task to serialize
     *
     * @return array - the serialized representation of the task
     */
    public function serialize(Theme $theme)
    {
        return $this->serializer->serialize($theme);
    }

    /**
     * Creates a new theme.
     *
     * @return Theme
     */
    public function create(array $data)
    {
        return $this->update(new Theme(), $data);
    }

    /**
     * Updates an existing theme.
     *
     * @return Theme
     *
     * @throws InvalidDataException
     */
    public function update(Theme $theme, array $data)
    {
        $errors = $this->validate($data);
        if (count($errors) > 0) {
            throw new InvalidDataException('Theme is not valid', $errors);
        }

        $this->serializer->deserialize($data, $theme);

        // todo : create directory structure and files
        // todo : move files if name change

        $this->om->persist($theme);
        $this->om->flush();

        return $theme;
    }

    /**
     * Validates theme data.
     *
     * @return array
     */
    public function validate(array $data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = ['path' => '/name', 'message' => 'name can not be empty.'];
        }

        return $errors;
    }

    /**
     * Returns the names of all the registered themes.
     *
     * @param bool $onlyEnabled
     *
     * @return string[]
     */
    public function listThemeNames($onlyEnabled = false)
    {
        $themes = $this->all($onlyEnabled);
        $themeNames = [];

        /** @var Theme $theme */
        foreach ($themes as $theme) {
            $themeNames[$theme->getNormalizedName()] = $theme->getName();
        }

        return $themeNames;
    }

    /**
     * Returns the app theme directory.
     *
     * @return string
     */
    public function getThemeDir()
    {
        return $this->themeDir;
    }

    /**
     * @return bool
     */
    public function canEdit(Theme $theme, User $user)
    {
        return !(
            !empty($theme->getPlugin()) // plugin themes
            || (empty($theme->getUser()) && !$this->authorization->isGranted('ROLE_ADMIN')) // custom platform themes
            || (!empty($theme->getUser() && $user !== $theme->getUser())) // users themes
        );
    }

    /**
     * Deletes a theme, including its css directory.
     *
     * @param bool $skipErrors
     *
     * @throws \Exception if the theme is not a custom theme
     */
    public function delete(Theme $theme, User $user, $skipErrors = false)
    {
        if (!$this->canEdit($theme, $user)) {
            if (!$skipErrors) {
                throw new \Exception('You can not delete this theme.');
            } else {
                return;
            }
        }

        $this->om->remove($theme);
        $this->om->flush();

        // todo : to remove and delete src-files instead
        $fs = new Filesystem();
        $fs->remove("{$this->themeDir}/{$theme->getNormalizedName()}");
    }

    /**
     * Deletes a Theme.
     * It's only possible if the User owns it.
     *
     * @param array $themes - the uuids of themes to delete
     */
    public function deleteBulk(array $themes, User $user)
    {
        // Reload the list of questions to delete
        $toDelete = $this->repository->findByUuids($themes);

        $this->om->startFlushSuite();
        foreach ($toDelete as $theme) {
            $this->delete($theme, $user, true);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Returns the current platform theme.
     *
     * NB. This method is called many times
     *     in the platform execution (find theme assets, locate custom templates, etc).
     *     So we cache the current theme in the service to avoid many DB calls.
     *
     * @return Theme
     */
    public function getCurrentTheme()
    {
        if (empty($this->currentTheme)) {
            $this->currentTheme = $this->getThemeByNormalizedName(
                $this->config->getParameter('theme')
            );
        }

        return $this->currentTheme;
    }

    /**
     * Lists all themes installed in the current platform.
     *
     * @param bool $onlyEnabled
     *
     * @return Theme[]
     */
    public function all($onlyEnabled = false)
    {
        return $this->repository->findAll($onlyEnabled);
    }

    /**
     * Returns the default platform theme.
     *
     * @return Theme
     */
    public function getDefaultTheme()
    {
        $default = $this->repository->findOneBy(['default' => true]);

        if (!$default) {
            $default = $this->repository->findAll()[0];
        }

        return $default;
    }

    /**
     * Finds a theme by its name.
     *
     * @param string $name
     *
     * @return Theme
     */
    public function getThemeByName($name)
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    /**
     * Finds a theme by its normalized name.
     *
     * @param string $normalizedName
     *
     * @return Theme
     */
    public function getThemeByNormalizedName($normalizedName)
    {
        return $this->getThemeByName(
            ucwords(str_replace('-', ' ', $normalizedName))
        );
    }
}
