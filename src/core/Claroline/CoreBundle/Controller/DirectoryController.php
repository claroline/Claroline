<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DirectoryController extends Controller
{
    /*
     * indexAction for resourceController
     */
    public function indexAction($id)
    {
        return new Response("index");
    }
}