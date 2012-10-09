<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class JasmineController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Jasmine:spec_runner.html.twig');
    }
}