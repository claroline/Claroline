<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller of the platform homepage.
 */
class HomeController extends Controller
{
    /**
     * Displays the Perso desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return $this->render('ClarolineCoreBundle:Tool\desktop\home:perso.html.twig');
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return $this->render('ClarolineCoreBundle:Tool\desktop\home:info.html.twig');
    }
}