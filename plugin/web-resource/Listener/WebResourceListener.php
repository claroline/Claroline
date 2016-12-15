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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Form\FileType;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    /**
     * @var \ZipArchive
     */
    private $zip;

    /**
     * Path to directory where zip files are stored.
     *
     * @var string
     */
    private $zipPath;

    /**
     * Path to directory where uploaded files are stored.
     *
     * @var string
     */
    private $filesPath;

    private $defaultIndexFiles = [
        'web/SCO_0001/default.html',
        'web/SCO_0001/default.htm',
        'web/index.html',
        'web/index.htm',
        'index.html',
        'index.htm',
        'web/SCO_0001/Default.html',
        'web/SCO_0001/Default.htm',
        'web/Index.html',
        'web/Index.htm',
        'Index.html',
        'Index.htm',
    ];

    /**
     * Class constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->zipPath = $this->container->getParameter('claroline.param.uploads_directory').'/webresource/';
        $this->filesPath = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR;
        $this->tokenStorage = $this->container->get('security.token_storage');
        $this->workspaceManager = $this->container->get('claroline.manager.workspace_manager');
    }

    /**
     * @DI\Observe("create_form_claroline_web_resource")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new FileType(), new File());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_web_resource',
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_web_resource")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $workspace = $event->getParent()->getWorkspace();
        $form = $this->container->get('form.factory')->create(new FileType(), new File());
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$this->isZip($form->get('file')->getData())) {
                $error = $this->container->get('translator')->trans('not_a_zip', [], 'resource');
                $form->addError(new FormError($error));
            } else {
                $event->setResources([$this->create($form->get('file')->getData(), $workspace)]);
                $event->stopPropagation();

                return;
            }
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => $event->getResourceType(),
            ]
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_web_resource")
     *
     * @param \Claroline\CoreBundle\Event\CreateResourceEvent|\Claroline\CoreBundle\Event\OpenResourceEvent $event
     */
    public function onOpenWebResource(OpenResourceEvent $event)
    {
        $hash = $event->getResource()->getHashName();

        $content = $this->container->get('templating')->render(
            'ClarolineWebResourceBundle::webResource.html.twig',
            [
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $hash.'/'.$this->guessRootFileFromUnzipped($this->zipPath.$hash),
                '_resource' => $event->getResource(),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("export_scorm_claroline_web_resource")
     *
     * @param ExportScormResourceEvent $event
     */
    public function onExportScorm(ExportScormResourceEvent $event)
    {
        $resource = $event->getResource();
        $hash = $resource->getHashName();
        $filename = 'file_'.$resource->getResourceNode()->getId();

        $template = $this->container->get('templating')->render(
            'ClarolineWebResourceBundle:Scorm:export.html.twig',
            [
                'path' => $filename.'/'.$this->guessRootFileFromUnzipped($this->zipPath.$hash),
                '_resource' => $event->getResource(),
            ]
        );

        // Set export template
        $event->setTemplate($template);

        $event->addFile($filename, $this->zipPath.$hash, true);

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
     * Get all HTML files from a zip archive.
     *
     * @param string $directory
     *
     * @return array
     */
    private function getHTMLFiles($directory)
    {
        $dir = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::NEW_CURRENT_AND_KEY);
        $files = new \RecursiveIteratorIterator($dir);

        $allowedExtensions = ['htm', 'html'];

        $list = [];
        foreach ($files as $file) {
            if (in_array($file->getExtension(), $allowedExtensions)) {
                // HTML File found
                $relativePath = str_replace($directory, '', $file->getPathname());
                $list[] = ltrim($relativePath, '\\/');
            }
        }

        return $list;
    }

    /**
     * Try to retrieve root file of the WebResource from the zip archive.
     *
     * @param UploadedFile $file
     *
     * @return string
     *
     * @throws \Exception
     */
    private function guessRootFile(UploadedFile $file)
    {
        if (!$this->getZip()->open($file)) {
            throw new \Exception('Can not open archive file.');
        }

        // Try to locate usual default HTML files to avoid unzip archive and scan directory tree
        foreach ($this->defaultIndexFiles as $html) {
            if (is_numeric($this->getZip()->locateName($html))) {
                return $html;
            }
        }

        // No default index file found => scan archive
        // Extract content into tmp dir
        $tmpDir = $this->zipPath.'tmp/'.$file->getClientOriginalName().'/';

        $this->getZip()->extractTo($tmpDir);
        $this->getZip()->close();

        // Search for root file
        $htmlFiles = $this->getHTMLFiles($tmpDir);

        // Remove tmp data
        $this->unzipDelete($tmpDir);

        // Only one file
        if (count($htmlFiles) === 1) {
            return array_shift($htmlFiles);
        }

        return;
    }

    /**
     * Try to retrieve root file of the WebResource from the unzipped directory.
     *
     * @param string $hash
     *
     * @return string
     */
    private function guessRootFileFromUnzipped($hash)
    {
        // Grab all HTML files from Archive
        $htmlFiles = $this->getHTMLFiles($hash);

        // Only one file
        if (count($htmlFiles) === 1) {
            return array_shift($htmlFiles);
        }

        // Check usual default root files
        foreach ($this->defaultIndexFiles as $file) {
            if (in_array($file, $htmlFiles)) {
                return $file;
            }
        }

        // Unable to find an unique HTML file
        return;
    }

    /**
     * Get ZipArchive object.
     *
     * @return \ZipArchive
     */
    private function getZip()
    {
        if (!$this->zip instanceof \ZipArchive) {
            $this->zip = new \ZipArchive();
        }

        return $this->zip;
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
     * Checks if a UploadedFile is a zip and contains index.html file.
     *
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return bool
     */
    private function isZip(UploadedFile $file)
    {
        $isZip = false;
        if ($file->getClientMimeType() === 'application/zip' || $this->getZip()->open($file) === true) {
            // Correct Zip type => check if html root file exists
            $rootFile = $this->guessRootFile($file);

            if (!empty($rootFile)) {
                $isZip = true;
            }
        }

        return $isZip;
    }

    public function create(UploadedFile $tmpFile, Workspace $workspace = null)
    {
        $file = new File();
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
     * Unzips files in web directory.
     *
     * Use first $this->getZip()->open($file) or $this->isZip($file)
     *
     * @param string $hash The hash name of the resource
     */
    private function unzip($hash)
    {
        if (!file_exists($this->zipPath.$hash)) {
            mkdir($this->zipPath.$hash, 0777, true);
        }
        $this->getZip()->open($this->filesPath.$hash);
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
     * Deletes web resource unzipped files.
     *
     * @param string $dir The path to the directory to delete
     */
    private function unzipDelete($dir)
    {
        foreach (glob($dir.'/*') as $file) {
            if (is_dir($file)) {
                $this->unzipDelete($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }
}
