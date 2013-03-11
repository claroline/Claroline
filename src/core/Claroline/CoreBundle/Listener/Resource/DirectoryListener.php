<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Form\DirectoryType;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceArrayEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceArrayEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DirectoryListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new DirectoryType, new Directory());
        $response = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container
            ->get('form.factory')
            ->create(new DirectoryType(), new Directory());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $event->setResource($form->getData());
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'directory'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $dir = $event->getResource();
        $file = $this->container->get('claroline.resource.exporter')->exportResources(array($dir->getId()));
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onExportArray(ExportResourceArrayEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $event->getResource();
        $children = $resourceRepo->findChildren($resource, array('ROLE_ADMIN'));
        $dataChildren = array();

        foreach ($children as $child) {
            $ed = $this->container->get('event_dispatcher');
            $newEvent = new ExportResourceArrayEvent($resourceRepo->find($child['id']));
            $ed->dispatch("export_{$child['type']}_array", $newEvent);
            $descr = $newEvent->getConfig();
            if (count($descr) > 0) {
                $dataChildren[] = $descr;
            }
        }

        $config = array('type' => 'directory', 'name' => $resource->getName());
        if (count($dataChildren) > 0) {
            $config['children'] = $dataChildren;
        }
        $event->setConfig($config);
        $event->stopPropagation();
    }

     public function onImportArray(ImportResourceArrayEvent $event)
     {
         $config = $event->getConfig();
         $manager = $this->container->get('claroline.resource.manager');
         $directory = new Directory();
         $directory->setName($config['name']);
         $manager->create($directory, $event->getParent()->getId(), 'directory');
         $ed = $this->container->get('event_dispatcher');

         if (isset($config['children'])) {
             foreach ($config['children'] as $child) {
                 $newEvent = new ImportResourceArrayEvent($child, $directory);
                 $ed->dispatch("import_{$child['type']}_array", $newEvent);
             }
         }
     }
}