<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DirectoryController extends Controller
{
    public function indexAction($id)
    {
        return new Response("index");
    }
}