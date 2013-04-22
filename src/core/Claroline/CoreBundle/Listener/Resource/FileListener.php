<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Library\Event\PlayFileEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;

/**
 * @DI\Service
 */
class FileListener implements ContainerAwareInterface
{
    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("create_form_file")
     *
     * @param CreateFormResourceEvent $event
     */
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

    /**
     * @DI\Observe("create_file")
     *
     * @param CreateResourceEvent $event
     */
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
            $tmpFile->move($this->container->getParameter('claroline.param.files_directory'), $hashName);
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

    /**
     * @DI\Observe("delete_file")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $pathName = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        if (file_exists($pathName)) {
            unlink($pathName);
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_file")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $newFile = $this->copy($event->getResource());
        $event->setCopy($newFile);
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($newFile);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_file")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $file = $event->getResource();
        $hash = $file->getHashName();
        $event->setItem(
            $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR . $hash
        );
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_file")
     *
     * @param OpenResourceEvent $event
     */
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
                    ->getParameter('claroline.param.files_directory') . $ds . $file->getHashName();
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

    /**
     * @DI\Observe("resource_file_to_template")
     *
     * @param ExportResourceTemplateEvent $event
     */
    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $resource = $event->getResource();
        $hash = $resource->getHashName();
        //@todo: remove this line without breaking everything ('type' is set by the tool listener).
        $config['type'] = 'file';
        $filePath = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR . $hash;
        $event->setFiles(array(array('archive_path' => $hash, 'original_path' => $filePath)));
        $event->setConfig($config);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource_file_from_template")
     *
     * @param ImportResourceTemplateEvent $event
     */
    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $files = $event->getFiles();
        $file = new File();
        $extension = pathinfo($files[0], PATHINFO_EXTENSION);
        $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
        $physicalPath = $this->container->getParameter('claroline.param.files_directory') . $ds . $hashName;
        rename($files[0], $physicalPath);
        $size = filesize($physicalPath);
        $file->setSize($size);
        $file->setHashName($hashName);
        $guesser = MimeTypeGuesser::getInstance();
        $file->setMimeType($guesser->guess($physicalPath));
        $event->setResource($file);
        $event->stopPropagation();
    }

    /**
     * Copies a file (no persistence).
     *
     * @param File $resource
     *
     * @return File
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
        $filePath = $this->container->getParameter('claroline.param.files_directory') . $ds . $resource->getHashName();
        $newPath = $this->container->getParameter('claroline.param.files_directory') . $ds . $hashName;
        copy($filePath, $newPath);

        return $newFile;
    }
}