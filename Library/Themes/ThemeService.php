<?php

namespace Claroline\CoreBundle\Library\Themes;

use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Claroline\CoreBundle\Entity\Theme\Theme;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.common.theme_service")
 */
class ThemeService
{
    private $container;
    private $themes;
    private $lessPath;
    private $themePath;

     /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
      */
    public function __construct($container)
    {
        $this->container = $container;
        $manager = $this->container->get('doctrine')->getManager();
        $this->themes = $manager->getRepository('ClarolineCoreBundle:Theme\Theme')->findAll();
        $this->themePath = __dir__.'/../../../../../../../web/themes/';
        $this->lessPath = $this->themePath.'less/';
        require_once dirname($this->container->get('kernel')->getRootDir()).'/vendor/leafo/lessphp/lessc.inc.php';
    }

    public function getThemePath()
    {
        return $this->themePath;
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
        foreach ($this->themes as $theme) {
            if ($theme->getId() == $id) {
                return $theme;
            }
        }

        return null;
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

        foreach ($this->themes as $theme) {

            if ($theme->getPath() === $filter ) {
                $tmp[$theme->getId()] = $theme;
            } elseif (!$filter) {
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

    public function findTheme($array)
    {
        $search = null;

        foreach ($this->themes as $theme) {
            $comparation = 0;

            foreach ($array as $key => $value) {
                if ($theme->get($key) === $value) {
                    $comparation++;
                }
            }

            if ($comparation === count($array)) {
                $search = $theme;
                break;
            }
        }

        return $search;
    }

    /**
     * Compile Less Themes that are defined in a twig file with lessphp filter
     *
     * @param mixed $themes An array of Theme entities or an strig of the template with following syntax:
     *                        'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig'
     */
    public function compileTheme($themes, $webPath = '.')
    {
        //@TODO Find something better for web path

        $lessGenerated = array();
        $twig = $this->container->get('twig');
        $twigLoader = $this->container->get('twig.loader');

        $assetic = $this->container->get('assetic.asset_manager');

        // enable loading assets from twig templates
        $assetic->setLoader('twig', new TwigFormulaLoader($twig));

        if (is_array($themes)) {
            foreach ($themes as $theme) {
                if ($theme->getPath() == 'less-generated') {
                    $lessGenerated[] = $theme->getName();
                } else {
                    $resource = new TwigResource($twigLoader, $theme->getPath());
                    $assetic->addResource($resource, 'twig');
                }
            }
        } else if (is_object($themes) and $themes->getPath() == 'less-generated') {
            $lessGenerated[] = $themes->getName();
        } else {
            $resource = new TwigResource($twigLoader, $themes);
            $assetic->addResource($resource, 'twig');
        }

        $this->compileRaw($lessGenerated);
        $writer = new AssetWriter($webPath);
        $writer->writeManagerAssets($assetic);
    }

    public function compileRaw($files)
    {
        foreach ($files as $file) {
            try {
                $folder = str_replace(' ', '-', strtolower($file));
                if (!file_exists($this->themePath.$folder)) {
                    mkdir($this->themePath.$folder, 0777, true);
                }

                $css = fopen($this->themePath.$folder.'/bootstrap.css', 'wb');
                $less = new \lessc;
                fwrite($css, $less->compileFile($this->lessPath.$folder.'/common.less'));
                fclose($css);
            } catch (exception $e) {
                throw \Exception("Fatal error" . $e->getMessage());
            }
        }
    }

    public function editTheme($variables, $name = null, $id = null)
    {
        $manager = $this->container->get('doctrine')->getManager();

        if ($id) {
            $theme = $this->getTheme($id);
        } else {
            $theme = new Theme('', '');
            $manager->persist($theme);
            $manager->flush();
        }

        if ($name) {
            $theme->setName($name);
        } else {
            $theme->setName('Theme'.$theme->getId());
        }

        $theme->setPath(
            'less-generated'
        );

        $path = $this->lessPath.str_replace(' ', '-', strtolower($theme->getName()));

        if ( !is_dir($path) ) {

            mkdir($path, 0777, true);
        }

        $vars = fopen($path.'/variables.less', 'w');
        $common = fopen($path.'/common.less', 'w');
        $themeless = fopen($path.'/theme.less', 'w');
        $twig = fopen($path.'/theme.html.twig', 'w');

        fwrite($vars, $variables);
        fwrite($common, $this->commonTemplate());
        fwrite($themeless, $this->themeTemplate());
        fwrite($twig, $this->twigTemplate($theme->getName()));

        fclose($vars);
        fclose($common);
        fclose($themeless);
        fclose($twig);

        $manager->persist($theme);
        $manager->flush();

        $this->compileTheme($theme);

        return $theme->getId();
    }

    public function deleteTheme($id = null)
    {
        $manager = $this->container->get('doctrine')->getManager();

        $theme = $this->getTheme($id);

        if ($theme) {

            $folder = str_replace(' ', '-', strtolower($theme->getName()));

            if ( is_dir($this->lessPath.$folder) ) {

                unlink($this->lessPath.$folder.'/variables.less');
                unlink($this->lessPath.$folder.'/common.less');
                unlink($this->lessPath.$folder.'/theme.less');
                unlink($this->lessPath.$folder.'/theme.html.twig');
                unlink($this->themePath.$folder.'/bootstrap.css');

                rmdir($this->lessPath.$folder);
                rmdir($this->themePath.$folder);

                $manager->remove($theme);
                $manager->flush();

                return 'true';
            }
        }

        return 'false';
    }

    public function themeTemplate()
    {
        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Theme:templates/theme.less.twig'
        );
    }

    public function commonTemplate()
    {
        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Theme:templates/common.less.twig'
        );
    }

    public function twigTemplate($path)
    {
        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Theme:templates/theme.html.twig',
            array('dirname' => $path)
        );
    }
}
