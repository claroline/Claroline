<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Themes;

use Doctrine\ORM\EntityManager;
use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Theme\Theme;

/**
 * @DI\Service("claroline.common.theme_service")
 */
class ThemeService
{
    private $em;
    private $container;
    private $lessPath;
    private $themePath;
    private $themes;

     /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager"),
     *     "container"  = @DI\Inject("service_container")
     * })
      */
    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->themePath = __DIR__.'/../../../../../../../web/themes/';
        $this->lessPath = $this->themePath . 'less/';
    }

    public function getLessPath()
    {
        return $this->lessPath;
    }

    /**
     * Get a theme by ID
     *
     */
    public function getTheme($id)
    {
        foreach ($this->retrieveThemes() as $theme) {
            if ($theme->getId() === intval($id)) {
                return $theme;
            }
        }
    }

    /**
     * Get the themes of the platform.
     *
     * @param  \String $filter Return only themes in a folder in views (Example: less-generated)
     * @return \Array  An array of Claroline\CoreBundle\Entity\Theme\Theme entities
     */
    public function getThemes($filter = null)
    {
        $tmp = array();

        foreach ($this->retrieveThemes() as $theme) {
            if ($theme->getPath() === $filter || !$filter) {
                $tmp[$theme->getId()] = $theme;
            }
        }

        return $tmp;
    }

    /**
     * List of themes.
     *
     * @param \String $themes An array with theme entities
     * @param \String $filter Return only themes in a folder in views (Example: less-generated)
     *
     * @return \Array a list with the paths of the themes.
     */
    public function listThemes($themes, $filter = null)
    {
        $tmp = array();

        foreach ($themes as $theme) {
            $tmp[$theme->getName()] = $theme->getPath();
        }

        return $tmp;
    }

    /**
     * @param array $filter The array that is used to filter an entity
     *                      (example: array('id' => 3, 'name' => 'Claroline'))
     * @return Theme|null
     */
    public function getThemeBy(array $filter)
    {
        return $this->em->getRepository('ClarolineCoreBundle:Theme\Theme')->findOneBy($filter);
    }

    public function editTheme($variables, $name = null, $id = null, $themeLess)
    {
        if ($id) {
            $theme = $this->getTheme($id);
        } else {
            $theme = new Theme('', '');
            $this->em->persist($theme);
            $this->em->flush();
        }

        if (!$themeLess) {
            $themeLess = $this->getThemeLessContent();
        }

        if ($name) {
            $theme->setName($name);
        } else {
            $theme->setName('Theme'.$theme->getId());
        }

        $theme->setPath('less-generated');
        $path = $this->lessPath . str_replace(' ', '-', strtolower($theme->getName()));

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        file_put_contents($path.'/variables.less', $variables);
        file_put_contents($path.'/common.less', $this->getCommonLessContent());
        file_put_contents($path.'/theme.less', $themeLess);
        file_put_contents($path.'/theme.html.twig', $this->renderThemeTemplate($theme->getName()));

        $this->compileRaw(array($theme->getName()));

        $this->em->persist($theme);
        $this->em->flush();

        return $theme->getId();
    }

    public function deleteTheme($id = null)
    {
        $theme = $this->getTheme($id);

        if ($theme) {
            $folder = str_replace(' ', '-', strtolower($theme->getName()));

            if (is_dir($this->lessPath.$folder)) {
                unlink($this->lessPath.$folder.'/variables.less');
                unlink($this->lessPath.$folder.'/common.less');
                unlink($this->lessPath.$folder.'/theme.less');
                unlink($this->lessPath.$folder.'/theme.html.twig');
                unlink($this->themePath.$folder.'/bootstrap.css');

                rmdir($this->lessPath.$folder);
                rmdir($this->themePath.$folder);

                $this->em->remove($theme);
                $this->em->flush();

                return 'true';
            }
        }

        return 'false';
    }

    /**
     * Compile Less Themes that are defined in a twig file with lessphp filter
     *
     * @param mixed  $themes  An array of Theme entities or a string of the template with following syntax:
     *                        'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig'
     * @param string $webPath
     *
     * @todo Find something better for web path
     */
    public function compileTheme($themes, $webPath = '.')
    {
        $assetManager = $this->container->get('assetic.asset_manager');
        $twigEnvironment = $this->container->get('twig');
        $twigLoader = $this->container->get('twig.loader');

        // enable loading assets from twig templates
        $assetManager->setLoader('twig', new TwigFormulaLoader($twigEnvironment));
        $lessGenerated = array();

        if (is_array($themes)) {
            foreach ($themes as $theme) {
                if ($theme->getPath() === 'less-generated') {
                    $lessGenerated[] = $theme->getName();
                } else {
                    $resource = new TwigResource($twigLoader, $theme->getPath());
                    $assetManager->addResource($resource, 'twig');
                }
            }
        } elseif (is_object($themes) and $themes->getPath() === 'less-generated') {
            $lessGenerated[] = $themes->getName();
        } else {
            $resource = new TwigResource($twigLoader, $themes);
            $assetManager->addResource($resource, 'twig');
        }

        $this->compileRaw($lessGenerated);
        $writer = new AssetWriter($webPath);
        $writer->writeManagerAssets($assetManager);
    }

    /**
     * Compiles a list of generated themes from the themes/less folder.
     *
     * @param array $themeNames The names of the themes, as stored in the database
     */
    public function compileRaw(array $themeNames)
    {
        foreach ($themeNames as $name) {
            $folder = str_replace(' ', '-', strtolower($name));

            if (!file_exists($this->themePath.$folder)) {
                mkdir($this->themePath.$folder, 0755, true);
            }

            $less = new \lessc;
            file_put_contents(
                $this->themePath.$folder.'/bootstrap.css',
                $less->compileFile($this->lessPath.$folder.'/common.less')
            );
        }
    }

    public function getThemeLessContent()
    {
        return file_get_contents(__DIR__ . '/../../Resources/views/Theme/templates/theme.less');
    }

    public function getCommonLessContent()
    {
        return file_get_contents(__DIR__ . '/../../Resources/views/Theme/templates/common.less');
    }

    private function renderThemeTemplate($path)
    {
        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Theme:templates/theme.html.twig',
            array('dirname' => $path)
        );
    }

    private function retrieveThemes()
    {
        if ($this->themes === null) {
            try {
                $this->themes = $this->em->getRepository('ClarolineCoreBundle:Theme\Theme')->findAll();
            } catch (\Exception $e) {
                return array();
            }
        }

        return $this->themes;
    }
}
