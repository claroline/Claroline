<?php

namespace ValidApplication\EligibleForIndex2\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    public function indexAction()
    {
        return new Response('EligibileForIndex2:MainController:indexAction:response');
    }
}