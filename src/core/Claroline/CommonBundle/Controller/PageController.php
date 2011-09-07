<?php

namespace Claroline\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * To be removed ASAP...
 * 
 * @todo Add a mechanism to configure and find the 'home' page (-> application) 
 */
class PageController extends Controller
{
    public function defaultIndexAction()
    {
        return $this->render('ClarolineCommonBundle::app_layout.html.twig');
    }
}