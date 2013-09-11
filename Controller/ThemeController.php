<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Themes\ThemeParameters;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 */
class ThemeController extends Controller
{
    /**
     * @Route("/list", name="claroline_admin_theme_list")
     *
     * @Template("ClarolineCoreBundle:Theme:list.html.twig")
     */
    public function indexAction()
    {
        $themes = $this->get('claroline.common.theme_service')->getThemes('less-generated');

        return array('themes' => $themes);
    }

    /**
     * @Route(
     *     "/edit/{id}",
     *     name="claroline_admin_theme_edit",
     *     defaults={ "id" = null }
     * )
     *
     * @Template()
     */
    public function editAction($id = null)
    {
        $variables = array();
        $file = null;
        $themes = $this->get('claroline.common.theme_service')->getThemes();

        if ($id and isset($themes[$id])) {

            $variables['theme'] = $themes[$id];

            $path = explode(':', $themes[$id]->getPath());
            $path = explode('/', $path[2]);

            $file = __DIR__."/../Resources/views/less-generated/$path[0]/variables.less";
        }

        $variables['parameters'] = new ThemeParameters($file);

        return $variables;
    }

    /**
     * @Route(
     *     "/preview/{id}",
     *     name="claroline_admin_theme_preview",
     *     defaults={ "id" = null }
     * )
     *
     * @Template()
     */
    public function previewAction($id)
    {
        return array('theme' => $this->get('claroline.common.theme_service')->getTheme($id));
    }

    /**
     * @Route(
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
                $this->get('request')->get('variables'),
                $this->get('request')->get('name'),
                $this->get('request')->get('theme-id')
            )
        );
    }

    /**
     * @Route(
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
