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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Validation\Exception\InvalidDataException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\Theme\ThemeRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.manager.theme_manager")
 */
class ThemeManager
{
    /** @var ObjectManager */
    private $om;
    /** @var ThemeRepository */
    private $repository;
    /** @var AuthorizationCheckerInterface  */
    private $authorization;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var string */
    private $themeDir;
    /** @var Theme */
    private $currentTheme;

    /**
     * ThemeManager constructor.
     *
     * @DI\InjectParams({
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "config"        = @DI\Inject("claroline.config.platform_config_handler"),
     *     "kernelDir"     = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param ObjectManager                 $om
     * @param AuthorizationCheckerInterface $authorization
     * @param PlatformConfigurationHandler  $config
     * @param string                        $kernelDir
     */
    public function __construct(
        ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $config,
        $kernelDir
    ) {
        $this->om = $om;
        $this->repository = $this->om->getRepository('ClarolineCoreBundle:Theme\Theme');
        $this->authorization = $authorization;
        $this->config = $config;
        $this->themeDir = $kernelDir.'/../web/themes';
    }

    /**
     * Creates a new theme.
     *
     * @param array $data
     *
     * @return Theme
     */
    public function create(array $data)
    {
        // todo : create directory structure and files

        return $this->update(new Theme(), $data);
    }

    /**
     * Updates an existing theme.
     *
     * @param Theme $theme
     * @param array $data
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

        $theme->setName($data['name']);

        // todo : update other themes props
        // todo : move files if name change

        $this->om->persist($theme);
        $this->om->flush();

        return $theme;
    }

    /**
     * Validates theme data.
     *
     * @param array $data
     *
     * @return array
     */
    public function validate(array $data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = [
                'path' => '/name',
                'message' => 'name can not be empty.',
            ];
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
     * Returns whether the theme directory is writable.
     *
     * @return bool
     */
    public function isThemeDirWritable()
    {
        return is_writable($this->themeDir);
    }

    /**
     * @param Theme $theme
     * @param User $user
     *
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
     * @param Theme $theme
     * @param User  $user
     * @param bool  $skipErrors
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
     * Deletes a Item.
     * It's only possible if the Item is not used in an Exercise.
     *
     * @param array $themes - the uuids of themes to delete
     * @param User  $user
     */
    public function deleteBulk(array $themes, User $user)
    {
        // Reload the list of questions to delete
        $toDelete = $this->repository->findByUuids($themes);
        foreach ($toDelete as $theme) {
            $this->delete($theme, $user, true);
        }

        $this->om->flush();
    }

    /**
     * Checks whether a theme is the current one.
     *
     * @param Theme $theme
     *
     * @return bool
     */
    public function isCurrentTheme(Theme $theme)
    {
        return $theme->getNormalizedName() === $this->config->getParameter('theme');
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
        return $this->repository->findBy($onlyEnabled ? ['enabled' => true] : []);
    }

    /**
     * Returns the default platform theme.
     *
     * @return Theme
     */
    public function getDefaultTheme()
    {
        return $this->repository->findOneBy(['default' => true]);
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
