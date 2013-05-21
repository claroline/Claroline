<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the platform homepage.
 */
class HomeController extends Controller
{
    /**
     * @Route(
     *     "/perso",
     *     name="claro_tool_desktop_perso"
     * )
     *
     * Displays the Perso desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return $this->render('ClarolineCoreBundle:Tool\desktop\home:perso.html.twig');
    }

    /**
     * @Route(
     *     "/info",
     *     name="claro_tool_desktop_info"
     * )
     *
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return $this->render('ClarolineCoreBundle:Tool\desktop\home:info.html.twig');
    }
}