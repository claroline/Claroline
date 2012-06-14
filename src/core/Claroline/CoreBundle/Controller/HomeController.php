<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller of the platform homepage.
 */
class HomeController extends Controller
{
    /**
     * Displays the homepage index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Home:index.html.twig');
    }
}