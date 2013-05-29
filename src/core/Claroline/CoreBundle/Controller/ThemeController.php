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
    public function compileThemes()
    {
        $webPath = $this->container->get('templating.helper.assets')->getUrl("");
        $webPath = substr($webPath, 0, -1);

        $webPath = "./";

        $twig = $this->container->get("twig");
        $twigLoader = $this->container->get("twig.loader");
        $template = "ClarolineCoreBundle:less:bootstrap-default/theme.html.twig";

        $am = $this->container->get("assetic.asset_manager");

        // enable loading assets from twig templates
        $am->setLoader('twig', new TwigFormulaLoader($twig));

        // loop through all your templates
        //foreach ($templates as $template) {
            $resource = new TwigResource($twigLoader, $template);
            $am->addResource($resource, 'twig');
        //}

        $writer = new AssetWriter($webPath);
        $writer->writeManagerAssets($am);

        return new Response("test");
    }
}
