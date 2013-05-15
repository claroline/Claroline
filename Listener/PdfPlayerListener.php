<?php

namespace Claroline\PdfPlayerBundle\Listener;

use Claroline\CoreBundle\Library\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class PdfPlayerListener extends ContainerAware
{
    public function onOpenPdf (PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        $content = $this->container->get('templating')->render(
            'ClarolinePdfPlayerBundle::pdf.html.twig',
            array(
                'workspace' => $event->getResource()->getWorkspace(),
                'path' => $path,
                'pdf' => $event->getResource(),
                '_resource' => $event->getResource()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}