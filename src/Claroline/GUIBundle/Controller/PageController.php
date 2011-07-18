<?php

namespace Claroline\GUIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PageController extends Controller
{
    public function defaultIndexAction()
    {
        return $this->render('ClarolineGUIBundle::app_layout.html.twig');
    }
}