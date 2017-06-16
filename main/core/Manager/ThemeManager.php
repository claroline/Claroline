<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @DI\Service("claroline.manager.theme_manager")
 */
class ThemeManager
{
    private static $stockThemes = [
        'Claroline',
        'Claroline Black',
        'Claroline Mint',
        'Claroline Ruby',
    ];

    private $om;
    private $config;
    private $themeDir;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "config"     = @DI\Inject("claroline.config.platform_config_handler"),
     *     "kernelDir"  = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param ObjectManager                $om
     * @param PlatformConfigurationHandler $config
     * @param string                       $kernelDir
     */
    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $config,
        $kernelDir
    ) {
        $this->om = $om;
        $this->config = $config;
        $this->themeDir = $kernelDir.'/../web/themes';
    }

    /**
     * Returns all the registered themes.
     *
     * @return Theme[]
     */
    public function listThemes()
    {
        return $this->om->getRepository('ClarolineCoreBundle:Theme\Theme')
            ->findBy([], ['name' => 'ASC']);
    }

    /**
     * Returns the names of all the registered themes.
     *
     * @param bool $customOnly
     *
     * @return string[]
     */
    public function listThemeNames($customOnly = false)
    {
        $themes = $this->listThemes();
        $themeNames = [];

        foreach ($themes as $theme) {
            //fetch stock themes from database or config.yml later
            if ($customOnly && in_array($theme->getName(), self::$stockThemes)) {
                continue;
            }

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
     * Deletes a theme, including its css directory.
     *
     * @param Theme $theme
     *
     * @throws \Exception if the theme is not a custom theme
     */
    public function deleteTheme(Theme $theme)
    {
        if (!$theme->isCustom()) {
            throw new \Exception("Stock theme '{$theme->getName()}' cannot be deleted");
        }

        $this->om->remove($theme);
        $this->om->flush();

        $fs = new Filesystem();
        $fs->remove("{$this->themeDir}/{$theme->getNormalizedName()}");
    }

    /**
     * Returns the current
     * platform theme.
     *
     * @return Theme
     */
    public function getCurrentTheme()
    {
        $name = ucwords(str_replace('-', ' ', $this->config->getParameter('theme')));

        return $this->om->getRepository('ClarolineCoreBundle:Theme\Theme')
            ->findOneBy(['name' => $name]);
    }

    /**
     * Creates a custom theme based on a css file.
     *
     * @param string $name
     * @param File   $file
     * @param bool   $extendDefault
     */
    public function createCustomTheme($name, File $file, $extendDefault = false)
    {
        $theme = new Theme();
        $theme->setName($name);
        $theme->setExtendingDefault($extendDefault);
        $themeDir = "{$this->themeDir}/{$theme->getNormalizedName()}";

        $fs = new Filesystem();
        $fs->mkdir($themeDir);

        $file->move($themeDir, 'bootstrap.css');

        $this->om->persist($theme);
        $this->om->flush();
    }

    public function getThemeByNormalizedName($name)
    {
        $themes = [];
        $allThemes = $this->om->getRepository('ClarolineCoreBundle:Theme\Theme')->findAll();

        /** @var Theme $theme */
        foreach ($allThemes as $theme) {
            $normalizedName = $theme->getNormalizedName();

            if ($normalizedName === $name) {
                $themes[] = $theme;
            }
        }

        $name = ucwords(str_replace('-', ' ', $this->config->getParameter('theme')));

        return $this->om->getRepository('ClarolineCoreBundle:Theme\Theme')
            ->findOneBy(['name' => $name]);

        return count($themes) > 0 ? $themes[count($themes) - 1] : null;
    }

    public static function listStockThemesName()
    {
        return self::$stockThemes;
    }
}
