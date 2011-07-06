<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PortalController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Portal:index.html.twig');
    }
}
