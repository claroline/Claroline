<?php

namespace Claroline\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClarolineRegisterBundle::index.html.twig');
    }
}