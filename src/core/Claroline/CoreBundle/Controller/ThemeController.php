<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Library\Themes\ThemeParameters;
use Claroline\CoreBundle\Library\Themes\ThemeCompile;

class ThemeController extends Controller
{
    /**
     * @route("/list", name="claroline_admin_theme_list")
     *
     */
    public function indexAction()
    {
        $themes = $this->get('claroline.common.theme_service')->getThemes();

        return $this->render('ClarolineCoreBundle:Theme:list.html.twig', array('themes' => $themes));
    }

    /**
     * @route(
     *     "/edit/{id}",
     *     name="claroline_admin_theme_edit",
     *     defaults={ "id" = null }
     * )
     *
     */
    public function editAction($id = null)
    {
        $variables = array();

        $themes = $this->get('claroline.common.theme_service')->getThemes();

        if ($id and isset($themes[$id])) {
            //$this->parse($themes[$id]);
             $variables['theme'] = $themes[$id];
        } else {
             $variables['parameters'] = new ThemeParameters();
        }

        return $this->render('ClarolineCoreBundle:Theme:edit.html.twig', $variables);
    }

    public function deleteAction()
    {
        echo "sdf";
    }

    /**
     * @route("/compile", name="claroline_admin_theme_compile")
     *
     */
    public function compileAction()
    {
        $this->get('claroline.common.theme_service')->compileTheme(
            "ClarolineCoreBundle:less:bootstrap-default/theme.html.twig"
        );

        return new Response("true");
    }
}
