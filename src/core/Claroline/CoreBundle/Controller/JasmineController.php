<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class JasmineController extends Controller
{
    /**
     * @Route(
     *     "/jasmine",
     *     name="claro_jasmine_spec_runner"
     * )
     */
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Jasmine:spec_runner.html.twig');
    }
}