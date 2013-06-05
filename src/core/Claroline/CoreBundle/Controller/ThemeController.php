<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;

class ThemeController extends Controller
{
    /**
     * @route("/compile", name="claroline_admin_theme_compile")
     *
     */
    public function compileAction()
    {
        $this->compileTheme("ClarolineCoreBundle:less:bootstrap-default/theme.html.twig");

        return new Response("Done!");
    }

    /**
     * Compile Less Themes that are defined in a twig file with lessphp filter
     *
     * @param mixed $template An strig or an array of routes to the template the following syntax:
     *                        "ClarolineCoreBundle:less:bootstrap-default/theme.html.twig"
     */
    private function compileTheme($template)
    {
        $webPath = "./"; //@TODO Find something better

        $twig = $this->container->get("twig");
        $twigLoader = $this->container->get("twig.loader");

        $assetic = $this->container->get("assetic.asset_manager");

        // enable loading assets from twig templates
        $assetic->setLoader('twig', new TwigFormulaLoader($twig));

        if (is_array($template)) {
            foreach ($template as $templateName) {
                $resource = new TwigResource($twigLoader, $templateName);
                $assetic->addResource($resource, 'twig');
            }
        } else {
            $resource = new TwigResource($twigLoader, $template);
            $assetic->addResource($resource, 'twig');
        }

        $writer = new AssetWriter($webPath);
        $writer->writeManagerAssets($assetic);
    }
}
