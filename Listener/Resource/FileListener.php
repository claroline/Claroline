<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;


/**
 * @DI\Service("claroline.listener.file_listener")
 */
class FileListener implements ContainerAwareInterface
{
    private $container;
    private $resourceManager;
    private $om;
    private $sc;

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
        $this->resourceManager = $container->get('claroline.manager.resource_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->sc = $container->get('security.context');
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
            'ClarolineCoreBundle:Resource:createForm.html.twig',
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
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form->getData();
            $tmpFile = $form->get('file')->getData();
            $fileName = $tmpFile->getClientOriginalName();
            $mimeType = $tmpFile->getClientMimeType();

            //uncompress
            if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip' && $form->get('uncompress')->getData()) {
                $roots = $this->unzip($tmpFile, $event->getParent());
                $event->setResources($roots);
                //do not process the resources afterwards because nodes have been created with the unzip function.
                $event->setProcess(false);
                $event->stopPropagation();
            } else {
                $file = $this->createFile($file, $tmpFile, $fileName, $mimeType);
                $event->setResources(array($file));
                $event->stopPropagation();
            }

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
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
        $pathName = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();

        if (file_exists($pathName)) {
            $event->setFiles(array($pathName));
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
        $resource = $event->getResource();
        $mimeType = $resource->getResourceNode()->getMimeType();
        $playEvent = $this->container->get('claroline.event.event_dispatcher')
                ->dispatch(
                    strtolower(str_replace('/', '_', 'play_file_' . $mimeType)),
                    'PlayFile',
                    array($resource)
                );

        if ($playEvent->getResponse() instanceof Response) {
            $response = $playEvent->getResponse();
        } else {
            $mimeElements = explode('/', $mimeType);
            $baseType = strtolower($mimeElements[0]);
            $fallBackPlayEventName = 'play_file_' . $baseType;
            $fallBackPlayEvent = $this->container->get('claroline.event.event_dispatcher')->dispatch(
                $fallBackPlayEventName,
                'PlayFile',
                array($resource)
            );
            if ($fallBackPlayEvent->getResponse() instanceof Response) {
                $response = $fallBackPlayEvent->getResponse();
            } else {
                $item = $this->container
                    ->getParameter('claroline.param.files_directory') . $ds . $resource->getHashName();
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
            ->get('claroline.utilities.misc')
            ->generateGuid() . '.' . pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
        $newFile->setHashName($hashName);
        $filePath = $this->container->getParameter('claroline.param.files_directory') . $ds . $resource->getHashName();
        $newPath = $this->container->getParameter('claroline.param.files_directory') . $ds . $hashName;
        copy($filePath, $newPath);

        return $newFile;
    }

    private function unzip($archivepath, ResourceNode $root)
    {
        $extractPath = sys_get_temp_dir() .
            DIRECTORY_SEPARATOR .
            $this->container->get('claroline.utilities.misc')->generateGuid() .
            '.zip';

        $archive = new \ZipArchive();

        if ($archive->open($archivepath) === TRUE) {
            $archive->extractTo($extractPath);
            $archive->close();
            $this->om->startFlushSuite();
            $perms = $this->container->get('claroline.manager.rights_manager')->getCustomRoleRights($root);
            $roots = $this->uploadDir($extractPath, $root, $perms);
            $this->om->endFlushSuite();

            return $roots;
        } else {
            throw new \Exception("The archive {$archivepath} can't be opened");
        }
    }

    private function uploadDir($dir, ResourceNode $parent, array $perms)
    {
        $roots = [];
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $roots[] = $this->uploadFile($item, $parent, $perms);
            }

            if ($item->isDir() === true && $item->isDot() !== true) {
                //create new dir
                $directory = new Directory();
                $directory->setName($item->getBasename());
                $roots[] = $this->resourceManager->create(
                    $directory,
                    $this->resourceManager->getResourceTypeByName('directory'),
                    $this->sc->getToken()->getUser(),
                    $parent->getWorkspace(),
                    $parent,
                    null,
                    $perms
                );

                $this->uploadDir(
                    $dir . DIRECTORY_SEPARATOR . $item->getBasename(),
                    $directory->getResourceNode(),
                    $perms
                );
            }
        }

        return $roots;
    }

    private function uploadFile(\DirectoryIterator $file, ResourceNode $parent, array $perms)
    {
        $entityFile = new File();
        $fileName = $file->getFilename();
        $size = @filesize($file);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = $this->container->get('claroline.utilities.mime_type_guesser')->guess($extension);
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() . "." . $extension;
        copy(
            $file->getPathname(),
            $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR. $hashName
        );
        $entityFile->setSize($size);
        $entityFile->setName($fileName);
        $entityFile->setHashName($hashName);
        $entityFile->setMimeType($mimeType);

        return $this->resourceManager->create(
            $entityFile,
            $this->resourceManager->getResourceTypeByName('file'),
            $this->sc->getToken()->getUser(),
            $parent->getWorkspace(),
            $parent,
            null,
            $perms
        );
    }

    public function createFile(File $file, SfFile $tmpFile, $fileName, $mimeType)
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() . "." . $extension;
        $tmpFile->move($this->container->getParameter('claroline.param.files_directory'), $hashName);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        return $file;
    }
}
