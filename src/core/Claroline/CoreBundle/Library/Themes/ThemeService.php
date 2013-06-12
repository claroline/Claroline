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
     * Get the themes of the platform.
     *
     * @param \String $filter Return only themes in a folder in views (Example: less-generated)
     * @return \Array An array of Claroline\CoreBundle\Entity\Theme\Theme entities
     */
    public function getThemes($filter = null)
    {
        $tmp = array();

        $manager = $this->container->get("doctrine")->getManager();

        $themes = $manager->getRepository("ClarolineCoreBundle:Theme\Theme")->findAll();

        foreach ($themes as $theme) {

            $path = explode(":", $theme->getPath());

            if ($filter and isset($path[1]) and $path[1] == $filter ) {
                $tmp[$theme->getId()] = $theme;
            } else if (!$filter) {
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
     * Compile Less Themes that are defined in a twig file with lessphp filter
     *
     * @param mixed $themes An array of Theme entities or an strig of the template with following syntax:
     *                        "ClarolineCoreBundle:less:bootstrap-default/theme.html.twig"
     */
    public function compileTheme($themes, $webPath = ".")
    {
        //@TODO Find something better for web path

        $twig = $this->container->get("twig");
        $twigLoader = $this->container->get("twig.loader");

        $assetic = $this->container->get("assetic.asset_manager");

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
}
