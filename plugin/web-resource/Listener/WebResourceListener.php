<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Listener;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service("claroline.listener.web_resource_listener")
 */
class WebResourceListener
{
    /**
     * Service container.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    private $webResourceManager;

    /**
     * Path to directory where zip files are stored.
     *
     * @var string
     */
    private $zipPath;

    /**
     * Class constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container"),
     *     "webResourceManager"    = @DI\Inject("claroline.manager.web_resource_manager"),
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(
      ContainerInterface $container,
      WebResourceManager $webResourceManager
      ) {
        $this->container = $container;
        $this->filesPath = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR;
        $this->tokenStorage = $this->container->get('security.token_storage');
        $this->workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $this->webResourceManager = $webResourceManager;
    }

    /**
     * @DI\Observe("open_claroline_web_resource")
     *
     * @param \Claroline\CoreBundle\Event\CreateResourceEvent|\Claroline\CoreBundle\Event\Resource\OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $hash = $event->getResource()->getHashName();
        $workspace = $event->getResource()->getResourceNode()->getWorkspace();
        $zipPath = $this->container->getParameter('claroline.param.uploads_directory').DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR;
        $content = $this->container->get('templating')->render(
            'ClarolineWebResourceBundle:web-resource:open.html.twig',
            [
                'workspace' => $workspace,
                'path' => $zipPath.$hash.DIRECTORY_SEPARATOR.$this->webResourceManager->guessRootFileFromUnzipped($zipPath.$hash),
                '_resource' => $event->getResource(),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource.claroline_web_resource.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $hash = $event->getResource()->getHashName();
        $workspace = $event->getResource()->getResourceNode()->getWorkspace();
        $zipPath = $this->container->getParameter('claroline.param.uploads_directory').DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR;
        $event->setData([
          'path' => $zipPath.$hash.DIRECTORY_SEPARATOR.$this->webResourceManager->guessRootFileFromUnzipped($zipPath.$hash),
        ]);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_web_resource")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $file = $this->filesPath.$event->getResource()->getHashName();
        $unzipFile = $this->zipPath.$event->getResource()->getHashName();

        if (file_exists($file)) {
            $event->setFiles([$file]);
        }

        if (file_exists($unzipFile)) {
            $this->unzipDelete($unzipFile);
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_web_resource")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $file = $this->copy($event->getResource());
        $event->setCopy($file);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_claroline_web_resource")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $event->setItem($this->filesPath.$event->getResource()->getHashName());
        $event->stopPropagation();
    }

    /**
     * Returns a new hash for a file.
     *
     * @param mixed mixed The extension of the file or an Claroline\CoreBundle\Entity\Resource\File
     *
     * @return string
     */
    private function getHash($mixed)
    {
        if ($mixed instanceof File) {
            $mixed = pathinfo($mixed->getHashName(), PATHINFO_EXTENSION);
        }

        return $this->container->get('claroline.utilities.misc')->generateGuid().'.'.$mixed;
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
        $hash = $this->getHash($resource);

        $file = new File();
        $file->setSize($resource->getSize());
        $file->setName($resource->getName());
        $file->setMimeType($resource->getMimeType());
        $file->setHashName($hash);
        copy($this->filesPath.$resource->getHashName(), $this->filesPath.$hash);
        $this->getZip()->open($this->filesPath.$hash);
        $this->unzip($hash);

        return $file;
    }
}
