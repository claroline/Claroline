<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;

class DesktopController extends Controller
{
    public function indexAction()
    {
        $this->get('claroline.common.history_browser')->keepCurrentContext('desktop');
        return $this->render('ClarolineCoreBundle:Desktop:desktop.html.twig');
    }
}