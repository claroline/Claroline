<?php

namespace Claroline\CoreBundle\Library\View;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterViewHandler
{
    public function createResponse(ViewHandler $handler, View $view, Request $request, $format)
    {
        $data = $view->getData();
        $context = $view->getSerializationContext();
        $container = $handler->getContainer();
        $format = $view->getFormat();
        $file = $container->get('claroline.library.view.serializer.serializer')->serialize($data, $format);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=file.'.$format);

        switch ($format) {
            case 'csv': $response->headers->set('Content-Type', 'text/csv'); break;
            case 'xls': $response->headers->set('Content-Type', 'application/vnd.ms-excel'); break;
        }

        return $response;
    }
}
