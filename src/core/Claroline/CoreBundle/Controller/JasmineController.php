<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class JasmineController extends Controller
{
    /**
     * @Route(
     *     "/jasmine",
     *     name="claro_jasmine_spec_runner"
     * )
     *
     * @Template("ClarolineCoreBundle:Jasmine:specRunner.html.twig")
     */
    public function indexAction()
    {
        return array();
    }
}