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
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\FileType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Service
 */
class WebResourceListener implements ContainerAwareInterface
{
    private $container;
    private $zip;
    private $zipPath;
    private $filesPath;

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
        $this->zipPath = __DIR__ . '/../../../../../../web/uploads/webresource/';
        $this->filesPath = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR;
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
                $error = $this->container->get('translator')->trans('not_a_zip', array(), 'resource');
                $form->addError(new FormError($error));
            } else {
                $event->setResources(array($this->createResource($form)));
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
        $content = $this->container->get('templating')->render(
            'ClarolineWebResourceBundle::webResource.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $this->getIndexHTML($event->getResource()->getHashName()),
                'resource' => $event->getResource(),
                '_resource' => $event->getResource()
            )
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @Observe("delete_claroline_web_resource")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $file = $this->filesPath.$event->getResource()->getHashName();
        $unzipFile = $this->zipPath.$event->getResource()->getHashName();

        if (file_exists($file)) {
            $event->setFiles(array($file));
        }

        if (file_exists($unzipFile)) {
            $this->unzipDelete($unzipFile);
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
        $file = $this->copy($event->getResource());
        $event->setCopy($file);
        $event->stopPropagation();
    }

    /**
     * @Observe("download_claroline_web_resource")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $event->setItem($this->filesPath.$event->getResource()->getHashName());
        $event->stopPropagation();
    }

    /**
     * Get the HTML index file of a web resource.
     *
     * @param $path The path of a unziped web resource directory.
     */
    private function getIndexHTML($hash)
    {
        $files = [
            '/web/SCO_0001/default.html',
            '/web/SCO_0001/default.htm',
            '/web/index.html',
            '/web/index.htm',
            '/index.html',
            '/index.htm',
        ];

        foreach ($files as $file) {
            if (file_exists($this->zipPath.$hash.$file)) {
                return $hash.$file;
            }
        }
    }

    /**
     * Get ZipArchive object.
     */
    private function getZip()
    {
        if (!$this->zip instanceof \ZipArchive) {
            $this->zip = new \ZipArchive;
        }

        return $this->zip;
    }

    /**
     * Get a new hash for a file.
     *
     * @param $mixed The extention of the file or an Claroline\CoreBundle\Entity\Resource\File
     */
    private function getHash($mixed)
    {
        if ($mixed instanceof File) {
            $mixed = pathinfo($mixed->getHashName(), PATHINFO_EXTENSION);
        }

        return $this->container->get('claroline.utilities.misc')->generateGuid() . '.' . $mixed;
    }

    /**
     * Check if a UploadedFile is a zip and contains index.html file.
     *
     * @param $file Symfony\Component\HttpFoundation\File\UploadedFile.
     *
     * @return boolean.
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
     * Create a Web Resource from a valid form containing the zip file.
     *
     * @param $form a Symfony\Component\Form\Form
     *
     * @return Claroline\CoreBundle\Entity\Resource\File
     */
    private function createResource($form)
    {
        $file = $form->getData();
        $tmpFile = $form->get('file')->getData();
        $fileName = $tmpFile->getClientOriginalName();
        $hash = $this->getHash(pathinfo($fileName, PATHINFO_EXTENSION));
        $file->setSize(filesize($tmpFile));
        $file->setName($fileName);
        $file->setHashName($hash);
        $file->setMimeType('custom/claroline_web_resource');
        $tmpFile->move($this->filesPath, $hash);
        $this->unzip($hash);

        return $file;
    }

    /**
     * Unzip files in web directory.
     *
     * Use first $this->getZip()->open($file) or $this->isZip($file)
     *
     * @param $hash The hash name of the reource.
     */
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
        copy($this->filesPath.$resource->getHashName(), $this->filesPath.$hash);
        $this->getZip()->open($this->filesPath.$hash);
        $this->unzip($hash);

        return $file;
    }

    /**
     * Delete Web Resource unzip files and its contents.
     *
     * @param $dir The path to the directory to delete.
     */
    private function unzipDelete($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $this->unzipDelete($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }
}
