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

use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Form\FileType;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\Form\FormError;

/**
 * @Service
 */
class WebResourceListener implements ContainerAwareInterface
{
    private $container;
    private $zip;
    private $zipPath;

    public function __construct()
    {
        $this->zipPath = __DIR__ . '/../../../../../../web/uploads/webresource/';
    }

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @Observe("create_form_claroline_web_resource")
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
                'resourceType' => 'claroline_web_resource'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @Observe("create_claroline_web_resource")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new FileType, new File());
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$this->isZip($form->get('file')->getData())) {
                $form->addError(
                    new FormError(
                        $this->container->get('translator')->trans('The file must be a zip', array(), 'resource')
                    )
                );
            } else {
                $file = $this->createResource($form);
                $event->setResources(array($file));
                $event->stopPropagation();

                return;
            }
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
     * @Observe("open_claroline_web_resource")
     *
     * @param CreateResourceEvent $event
     */
    public function onOpenWebResource(OpenResourceEvent $event)
    {
        /*$path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();

        $content = $this->container->get('templating')->render(
            'ClarolineWebResourceBundle::webResource.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'resource' => $event->getResource()
            )
        );*/

        $event->setResponse(new Response('titi'));
    }

    /**
     * @Observe("delete_claroline_web_resource")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $file = $this->getFilesPath().$event->getResource()->getHashName();

        if (file_exists($file)) {
            $event->setFiles(array($file));
        }

        $event->stopPropagation();
    }

    /**
     * @Observe("copy_claroline_web_resource")
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
     *
     */
    private function getZip()
    {
        if (!$this->zip instanceof \ZipArchive) {
            $this->zip = new \ZipArchive;
        }

        return $this->zip;
    }

    /**
     *
     */
    private function getFilesPath()
    {
        return $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR;
    }

    /**
     *
     */
    private function getHash($mixed)
    {
        if ($mixed instanceof File) {
            $mixed = pathinfo($mixed->getHashName(), PATHINFO_EXTENSION);
        }

        return $this->container->get('claroline.utilities.misc')->generateGuid() . '.' . $mixed;
    }

    /**
     * @param $file Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private function isZip($file)
    {
        return (
            $file->getClientMimeType() === 'application/zip' and
            $this->getZip()->open($file) === true and
            (is_numeric($this->getZip()->locateName('web/index.html')) or
            is_numeric($this->getZip()->locateName('web/index.htm')) or
            is_numeric($this->getZip()->locateName('index.html')) or
            is_numeric($this->getZip()->locateName('index.htm')))
        );
    }

    /**
     *
     */
    private function createResource($form)
    {
        $file = $form->getData();
        $tmpFile = $form->get('file')->getData();
        $fileName = $tmpFile->getClientOriginalName();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $hash = $this->getHash($extension);
        $file->setSize(filesize($tmpFile));
        $file->setName(str_replace('.' . $extension, '', $fileName));
        $file->setHashName($hash);
        $file->setMimeType($tmpFile->getClientMimeType());
        $tmpFile->move($this->getFilesPath(), $hash);
        $this->unzip($hash);

        return $file;
    }

    private function unzip($hash)
    {
        if (!file_exists($this->zipPath.$hash)) {
            mkdir($this->zipPath.$hash, 0777, true);
        }
        $this->getZip()->extractTo($this->zipPath.$hash);
        $this->getZip()->close();
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
        copy($this->getFilesPath().$resource->getHashName(), $this->getFilesPath().$hash);

        return $file;
    }
}
