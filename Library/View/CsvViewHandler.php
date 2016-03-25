<?php

namespace Claroline\CoreBundle\Library\View;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CsvViewHandler
{
    public function createResponse(ViewHandler $handler, View $view, Request $request, $format)
    {
        $data = $view->getData();
        $context = $view->getSerializationContext();

        //$clone = clone $view;
        $view->setFormat('json');
        $json = $handler->handle($view, $request);
        $array = json_decode($json->getContent(), true);
        //var_dump($json->getContent());
        return new Response(print_r($array, true));
    }
}
