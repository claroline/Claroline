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

     /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
      */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Get a theme by ID
     *
     */
    public function getTheme($id)
    {
        $manager = $this->container->get('doctrine')->getManager();

        return $manager->getRepository('ClarolineCoreBundle:Theme\Theme')->find($id);
    }

    /**
     * Get the themes of the platform.
     *
     * @param \String $filter Return only themes in a folder in views (Example: less-generated)
     * @return \Array An array of Claroline\CoreBundle\Entity\Theme\Theme entities
     */
    public function getThemes($filter = null)
    {
        $tmp = array();

        $manager = $this->container->get('doctrine')->getManager();

        $themes = $manager->getRepository('ClarolineCoreBundle:Theme\Theme')->findAll();

        foreach ($themes as $theme) {

            $path = explode(':', $theme->getPath());

            if ($filter and isset($path[1]) and $path[1] == $filter ) {
                $tmp[$theme->getId()] = $theme;
            } else if (!$filter) {
                $tmp[$theme->getId()] = $theme;
            }
        }

        return $tmp;
    }

    public function findTheme($array)
    {
        $manager = $this->container->get('doctrine')->getManager();

        return $manager->getRepository('ClarolineCoreBundle:Theme\Theme')->findOneBy($array);
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
     * Compile Less Themes that are defined in a twig file with lessphp filter
     *
     * @param mixed $themes An array of Theme entities or an strig of the template with following syntax:
     *                        'ClarolineCoreBundle:less:bootstrap-default/theme.html.twig'
     */
    public function compileTheme($themes, $webPath = '.')
    {
        //@TODO Find something better for web path

        $twig = $this->container->get('twig');
        $twigLoader = $this->container->get('twig.loader');

        $assetic = $this->container->get('assetic.asset_manager');

        // enable loading assets from twig templates
        $assetic->setLoader('twig', new TwigFormulaLoader($twig));

        if (is_array($themes)) {
            foreach ($themes as $theme) {
                $resource = new TwigResource($twigLoader, $theme->getPath());
                $assetic->addResource($resource, 'twig');
            }
        } else {
            $resource = new TwigResource($twigLoader, $themes);
            $assetic->addResource($resource, 'twig');
        }

        $writer = new AssetWriter($webPath);
        $writer->writeManagerAssets($assetic);
    }

    public function editTheme($variables, $name = null, $id = null)
    {
        $manager = $this->container->get('doctrine')->getManager();

        if ($id) {

            $theme = $manager->getRepository('ClarolineCoreBundle:Theme\Theme')->find($id);

        } else {

            $theme = new Theme('', '');
            $manager->persist($theme);
            $manager->flush();
        }

        $path = 'Theme'.$theme->getId();

        if ($name) {

            $theme->setName($name);

        } else {

            $theme->setName($path);
        }

        $theme->setPath("ClarolineCoreBundle:less-generated:$path/theme.html.twig");

        $dirname = __dir__."/../../Resources/views/less-generated/$path";

        if ( !is_dir($dirname) ) {

            mkdir($dirname, 0755, true);
        }

        $vars = fopen($dirname.'/variables.less', 'w');
        $common = fopen($dirname.'/common.less', 'w');
        $themeless = fopen($dirname.'/theme.less', 'w');
        $twig = fopen($dirname.'/theme.html.twig', 'w');

        fwrite($vars, $variables);
        fwrite($common, $this->commonTemplate());
        fwrite($themeless, $this->themeTemplate());
        fwrite($twig, $this->twigTemplate($path));

        fclose($vars);
        fclose($common);
        fclose($themeless);
        fclose($twig);

        $manager->persist($theme);
        $manager->flush();

        $this->compileTheme($theme->getPath());

        return $theme->getId();
    }

    public function deleteTheme($id = null)
    {
        $manager = $this->container->get('doctrine')->getManager();

        $theme = $manager->getRepository('ClarolineCoreBundle:Theme\Theme')->find($id);

        if ($theme) {

            $path = 'Theme'.$theme->getId();

            $dirname = __dir__."/../../Resources/views/less-generated/$path";

            if ( is_dir($dirname) ) {

                unlink($dirname.'/variables.less');
                unlink($dirname.'/common.less');
                unlink($dirname.'/theme.less');
                unlink($dirname.'/theme.html.twig');

                rmdir($dirname);

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

    public function twigTemplate($dirname)
    {
        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Theme:templates/theme.html.twig',
            array('dirname' => $dirname)
        );
    }
}
