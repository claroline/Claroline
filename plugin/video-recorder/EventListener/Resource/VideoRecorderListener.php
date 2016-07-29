<?php

namespace Innova\VideoRecorderBundle\EventListener\Resource;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Innova\VideoRecorderBundle\Manager\VideoRecorderManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 *  @DI\Service()
 */
class VideoRecorderListener
{
    private $container;
    private $manager;

    /**
     * @DI\InjectParams({
     *      "container" = @DI\Inject("service_container"),
     *      "manager" = @DI\Inject("innova.video_recorder.manager")
     * })
     */
    public function __construct(ContainerInterface $container, VideoRecorderManager $manager)
    {
        $this->container = $container;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("open_innova_video_recorder")
     * Fired when a ResourceNode of type VideoFile is opened
     *
     * @param \Claroline\CoreBundle\Event\OpenResourceEvent $event
     *
     * @throws \Exception
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $resource = $event->getResource();
        $route = $this->container
                ->get('router')
                ->generate('claro_resource_open', [
            'node' => $resource->getResourceNode()->getId(),
            'resourceType' => 'file',
                ]
        );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_innova_video_recorder")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $formData = $request->request->all();
        $video = $request->files->get('video');

        $workspace = $event->getParent()->getWorkspace();
        $result = $this->manager->uploadFileAndCreateResource($formData, $video, $workspace);
        if (!is_null($result['errors']) && count($result['errors']) > 0) {
            $msg = $result['errors'][0];
            $event->setErrorFormContent($msg);
        }
        $file = $result['file'];

        $event->setPublished(true);
        $event->setResourceType('file');
        $event->setResources([$file]);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_form_innova_video_recorder")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $config = $this->manager->getConfig();
        // Create form POPUP
        $content = $this->container->get('templating')->render(
                'InnovaVideoRecorderBundle:VideoRecorder:form.html.twig',
                [
                  'resourceType' => 'innova_video_recorder',
                  'maxTime' => $config->getMaxRecordingTime(),
                ]
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_videorecorderbundle")
     */
    public function onConfig(PluginOptionsEvent $event)
    {
        $params = [];
        $params['_controller'] = 'InnovaVideoRecorderBundle:VideoRecorder:pluginConfigureForm';
        $subRequest = $this->container->get('request')->duplicate([], null, $params);
        $response = $this->container->get('http_kernel')->handle($subRequest, KernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_innova_video_recorder")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $pathName = $this->container->getParameter('claroline.param.files_directory').
                DIRECTORY_SEPARATOR.
                $event->getResource()->getHashName();

        if (file_exists($pathName)) {
            $event->setFiles([$pathName]);
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_innova_video_recorder")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $newFile = $this->copy($event->getResource(), $event->getParent());
        $event->setCopy($newFile);
        $event->stopPropagation();
    }

    /**
     * Copies a file (no persistence).
     *
     * @param File $resource
     *
     * @return File
     */
    private function copy(File $resource, ResourceNode $destParent)
    {
        $ds = DIRECTORY_SEPARATOR;
        $workspace = $destParent->getWorkspace();
        $newFile = new File();
        $newFile->setSize($resource->getSize());
        $newFile->setName($resource->getName());
        $newFile->setMimeType($resource->getMimeType());
        $hashName = 'WORKSPACE_'.$workspace->getId().
                $ds.
                $this->container->get('claroline.utilities.misc')->generateGuid().
                '.'.
                pathinfo($resource->getHashName(), PATHINFO_EXTENSION);
        $newFile->setHashName($hashName);
        $fileDir = $this->container->getParameter('claroline.param.files_directory');
        $filePath = $fileDir.$ds.$resource->getHashName();
        $newPath = $fileDir.$ds.$hashName;
        $workspaceDir = $fileDir.$ds.'WORKSPACE_'.$workspace->getId();

        if (!is_dir($workspaceDir)) {
            mkdir($workspaceDir);
        }
        copy($filePath, $newPath);

        return $newFile;
    }

    /**
     * @DI\Observe("download_innova_video_recorder")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $event->setItem(
                $this->container
                        ->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR.$event->getResource()->getHashName()
        );
        $event->stopPropagation();
    }
}
