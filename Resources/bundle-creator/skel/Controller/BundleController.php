<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class [[Bundle]]Controller extends Controller
{
    /**
     * @EXT\Route("/index", name="[[vendor]]_[[bundle]]_index")
     * @EXT\Template
     *
     * @return Response
     */
    public function indexAction()
    {
        throw new \Exception('hello');
    }
}
