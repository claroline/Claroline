<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
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
        $themes = $this->get('claroline.common.theme_service')->getThemes("less-generated");

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
        $file = null;

        $themes = $this->get('claroline.common.theme_service')->getThemes();

        if ($id and isset($themes[$id])) {

            $variables['theme'] = $themes[$id];

            $path = explode(":", $themes[$id]->getPath());
            $path = explode("/", $path[2]);

            $file = __dir__."/../Resources/views/less-generated/$path[0]/variables.less";
        }

        $variables['parameters'] = new ThemeParameters($file);

        return $this->render('ClarolineCoreBundle:Theme:edit.html.twig', $variables);
    }

    /**
     * @route(
     *     "/preview/{id}",
     *     name="claroline_admin_theme_preview",
     *     defaults={ "id" = null }
     * )
     *
     */
    public function previewAction($id)
    {
        return $this->render(
            'ClarolineCoreBundle:Theme:preview.html.twig',
            array('theme' => $this->get('claroline.common.theme_service')->getTheme($id))
        );
    }

    /**
     * @route(
     *     "/build/{id}",
     *     name="claroline_admin_theme_build",
     *     defaults={ "id" = null }
     * )
     *
     */
    public function buildAction($id = null)
    {
        return new Response(
            $this->get('claroline.common.theme_service')->editTheme(
                $this->get('request')->get("variables"),
                $this->get('request')->get("name"),
                $this->get('request')->get("theme-id")
            )
        );
    }

    /**
     * @route(
     *     "/delete/{id}",
     *     name="claroline_admin_theme_delete",
     *     defaults={ "id" = null }
     * )
     *
     */
    public function deleteAction($id = null)
    {
        return new Response($this->get('claroline.common.theme_service')->deleteTheme($id));
    }
}
