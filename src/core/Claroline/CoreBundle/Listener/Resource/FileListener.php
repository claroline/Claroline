<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceEvent;
use Claroline\CoreBundle\Library\Event\PlayFileEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceArrayEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceArrayEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class FileListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new FileType, new File());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'file'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new FileType, new File());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $file = $form->getData();
            $tmpFile = $form->get('file')->getData();
            $fileName = $tmpFile->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $size = filesize($tmpFile);
            $mimeType = $tmpFile->getClientMimeType();
            $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
            $tmpFile->move($this->container->getParameter('claroline.files.directory'), $hashName);
            $file->setSize($size);
            $file->setName($fileName);
            $file->setHashName($hashName);
            $file->setMimeType($mimeType);
            $event->setResource($file);
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => $event->getResourceType()
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $pathName = $this->container->getParameter('claroline.files.directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        if (file_exists($pathName)) {
            unlink($pathName);
        }

        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $newFile = $this->copy($event->getResource());
        $event->setCopy($newFile);
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($newFile);
        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        $file = $event->getResource();
        $hash = $file->getHashName();
        $event->setItem($this->container->getParameter('claroline.files.directory') . DIRECTORY_SEPARATOR . $hash);
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $file = $event->getResource();
        $mimeType = $file->getMimeType();
        $playEvent = new PlayFileEvent($file);
        $eventName = strtolower(str_replace('/', '_', 'play_file_'.$mimeType));
        $this->container->get('event_dispatcher')->dispatch($eventName, $playEvent);

        if ($playEvent->getResponse() instanceof Response) {
            $response = $playEvent->getResponse();
        } else {
            $fallBackPlayEvent = new PlayFileEvent($file);
            $mimeElements = explode('/', $mimeType);
            $baseType = strtolower($mimeElements[0]);
            $fallBackPlayEventName = 'play_file_'.$baseType;
            $this->container->get('event_dispatcher')->dispatch($fallBackPlayEventName, $fallBackPlayEvent);
            if ($fallBackPlayEvent->getResponse() instanceof Response) {
                $response = $fallBackPlayEvent->getResponse();
            } else {
                $item = $this->container
                    ->getParameter('claroline.files.directory') . $ds . $file->getHashName();
                $file = file_get_contents($item);
                $response = new Response();
                $response->setContent($file);
                $response->headers->set(
                    'Content-Transfer-Encoding',
                    'octet-stream'
                );
                $response->headers->set(
                    'Content-Type',
                    'application/force-download'
                );
                $response->headers->set(
                    'Content-Disposition',
                    'attachment; filename=file.'.pathinfo($item, PATHINFO_EXTENSION)
                );
                $response->headers->set(
                    'Content-Type',
                    'application/' . pathinfo($item, PATHINFO_EXTENSION)
                );
                $response->headers->set(
                    'Connection',
                    'close'
                );
            }
        }

        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onExportArray(ExportResourceArrayEvent $event)
    {
        $resource = $event->getResource();
        $config['type'] = 'file';
        $config['id'] = $resource->getId();
        $event->setConfig($config);
        $event->stopPropagation();
    }

    public function onImportArray(ImportResourceArrayEvent $event)
    {
        $config = $event->getConfig();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $file = $em->getRepository('ClarolineCoreBundle:Resource\File')->find($config['id']);
        $newFile = $this->copy($file);
        $manager = $this->container->get('claroline.resource.manager');
        $manager->create($newFile, $event->getParent()->getId(), 'file');
        $event->stopPropagation();
    }

    /**
     * Copy a file (no persistence).
     * @param \Claroline\CoreBundle\Listener\Resource\AbstractResource $resource
     * @return \Claroline\CoreBundle\Entity\Resource\File
     */
    private function copy(File $resource)
    {
        $ds = DIRECTORY_SEPARATOR;
        $newFile = new File();
        $newFile->setSize($resource->getSize());
        $newFile->setName($resource->getName());
        $newFile->setMimeType($resource->getMimeType());
        $hashName = $this->container
            ->get('claroline.resource.utilities')
            ->generateGuid() . '.' . pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
        $newFile->setHashName($hashName);
        $filePath = $this->container->getParameter('claroline.files.directory') . $ds . $resource->getHashName();
        $newPath = $this->container->getParameter('claroline.files.directory') . $ds . $hashName;
        copy($filePath, $newPath);

        return $newFile;
    }
}