<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Scorm12;
use Claroline\ScormBundle\Entity\Scorm12Tracking;
use Claroline\ScormBundle\Form\ScormType;
use Claroline\ScormBundle\Listener\Exception\InvalidScorm12ArchiveException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;

/**
 * @DI\Service
 */
class Scorm12Listener
{
    private $container;
    // path to the Scorm archive file
    private $filePath;
    private $formFactory;
    private $om;
    private $request;
    private $router;
    private $scormTrackingRepo;
    private $scormRepo;
    // path to the Scorm unzipped files
    private $scormResourcesPath;
    private $securityContext;
    private $templating;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "container"          = @DI\Inject("service_container"),
     *     "formFactory"        = @DI\Inject("form.factory"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "router"             = @DI\Inject("router"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "templating"         = @DI\Inject("templating"),
     *     "translator"         = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        SecurityContextInterface $securityContext,
        TwigEngine $templating,
        Translator $translator
    )
    {
        $this->container = $container;
        $this->filePath = $this->container
            ->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->scormTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm12Tracking');
        $this->scormRepo = $om->getRepository('ClarolineScormBundle:Scorm12');
        $this->scormResourcesPath = $this->container
            ->getParameter('kernel.root_dir') . '/../web/uploads/scormresources/';
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("create_form_claroline_scorm_12")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ScormType(), new Scorm12());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_scorm_12'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_scorm_12")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ScormType(), new Scorm12());
        $form->handleRequest($this->request);

        try {
            if ($form->isValid()) {
                $tmpFile = $form->get('file')->getData();

                if ($this->isScormArchive($tmpFile)) {
                    $scormResources = $this->createScormResource($tmpFile);
                    $event->setResources($scormResources);
                    $event->stopPropagation();

                    return;
                }
            }
        } catch (InvalidScorm12ArchiveException $e) {
            $msg = $e->getMessage();
            $errorMsg = $this->translator->trans(
                $msg,
                array(),
                'resource'
            );
            $form->addError(new FormError($errorMsg));
        }
        $content = $this->templating->render(
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
     * @DI\Observe("open_claroline_scorm_12")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $scorm = $event->getResource();
        $scormPath = 'uploads/scormresources/'
            . $scorm->getHashName()
            . DIRECTORY_SEPARATOR
            . $scorm->getEntryUrl();

        $user = $this->securityContext->getToken()->getUser();
        $scormTracking = $this->scormTrackingRepo->findOneBy(
            array('user' => $user->getId(), 'scorm' => $scorm->getId())
        );

        if (is_null($scormTracking)) {
            $scormTracking = new Scorm12Tracking();
            $scormTracking->setUser($user);
            $scormTracking->setScorm($scorm);
            $scormTracking->setScoreRaw(-1);
            $scormTracking->setScoreMax(-1);
            $scormTracking->setScoreMin(-1);
            $scormTracking->setLessonStatus("not attempted");
            $scormTracking->setSuspendData("");
            $scormTracking->setEntry("ab-initio");
            $scormTracking->setLessonLocation("");
            $scormTracking->setCredit("no-credit");
            $scormTracking->setTotalTime(0);
            $scormTracking->setSessionTime(0);
            $scormTracking->setLessonMode("normal");
            $scormTracking->setExitMode("");
        }
        $content = $this->templating->render(
            'ClarolineScormBundle::scorm12.html.twig',
            array(
                'resource' => $scorm,
                '_resource' => $scorm,
                'scormTracking' => $scormTracking,
                'scormUrl' => $scormPath,
                'workspace' => $scorm->getResourceNode()->getWorkspace()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_scorm_12")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $hashName = $event->getResource()->getHashName();
        $scormArchiveFile = $this->filePath . $hashName;
        $scormResourcesPath = $this->scormResourcesPath . $hashName;

        $nbScorm = (int)($this->scormRepo->getNbScormWithHashName($hashName));

        if ($nbScorm === 1) {

            if (file_exists($scormArchiveFile)) {
                $event->setFiles(array($scormArchiveFile));
            }
            if (file_exists($scormResourcesPath)) {
                $this->deleteFiles($scormResourcesPath);
            }
        }
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_scorm_12")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new Scorm12();
        $copy->setHashName($resource->getHashName());
        $copy->setLaunchData($resource->getLaunchData());
        $copy->setMasteryScore($resource->getMasteryScore());
        $copy->setEntryUrl($resource->getEntryUrl());
        $copy->setName($resource->getName());
        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_claroline_scorm_12")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $event->setItem($this->filePath . $event->getResource()->getHashName());
        $event->stopPropagation();
    }

    /**
     * Checks if a UploadedFile is a zip archive that contains a
     * imsmanifest.xml file in its root directory.
     *
     * @param UploadedFile $file
     *
     * @return boolean.
     */
    private function isScormArchive(UploadedFile $file)
    {
        $zip = new \ZipArchive();
        $isScormArchive = $file->getClientMimeType() === 'application/zip'
            && $zip->open($file)
            && $zip->getStream("imsmanifest.xml");

        if (!$isScormArchive) {
            throw new InvalidScorm12ArchiveException('invalid_scorm_archive_message');
        }

        return true;
    }

    /**
     * Unzip a given ZIP file into the web resources directory
     *
     * @param UploadedFile $file
     * @param $hashName name of the destination directory
     */
    private function unzipScormArchive(UploadedFile $file, $hashName)
    {
        $zip = new \ZipArchive();
        $zip->open($file);
        $destinationDir = $this->scormResourcesPath . $hashName;

        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }
        $zip->extractTo($destinationDir);
        $zip->close();
    }

    /**
     * Manages creation of Scorm resources and their web resources
     *
     * @param UploadedFile $file
     *
     * @return Scorm resource or null.
     */
    private function createScormResource(UploadedFile $file)
    {
        $fileName = $file->getClientOriginalName();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $hashName = $this->container->get('claroline.utilities.misc')
            ->generateGuid() . "." . $extension;
        $resources = $this->generateResourcesFromScormArchive($file, $hashName);
        $this->unzipScormArchive($file, $hashName);
        // Move Scorm archive in the files directory
        $file->move($this->filePath, $hashName);

        return $resources;
    }

    /**
     * Parses imsmanifest.xml file of a Scorm archive and
     * creates Scorm resources defined in it.
     *
     * @param UploadedFile $file
     * @param $hashName
     *
     * @return array of Scorm resources
     */
    private function generateResourcesFromScormArchive(UploadedFile $file, $hashName)
    {
        $resources = array();
        $contents = '';
        $zip = new \ZipArchive();

        $zip->open($file);
        $stream = $zip->getStream("imsmanifest.xml");

        while (!feof($stream)) {
            $contents .= fread($stream, 2);
        }
        $dom = new \DOMDocument();
        $dom->loadXML($contents);

        $scormVersionElements = $dom->getElementsByTagName('schemaversion');

        if ($scormVersionElements->length > 0) {
            $scormVersion = $scormVersionElements->item(0)->textContent;

            if ($scormVersion !== '1.2') {
                throw new InvalidScorm12ArchiveException('invalid_scorm_version_12_message');
            }
        }
        
        $items = $dom->getElementsByTagName('item');
        $scoResources = $dom->getElementsByTagName('resource');

        if ($items->length > 0) {

            foreach ($items as $item) {
                $ref = $item->attributes->getNamedItem('identifierref');

                if (!is_null($ref)) {
                    $identifierRef = $ref->nodeValue;
                    $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
                    $launchDatas = $item->getElementsByTagNameNS(
                        $item->lookupNamespaceUri('adlcp'),
                        'datafromlms'
                    );
                    $masteryScores = $item->getElementsByTagNameNS(
                        $item->lookupNamespaceUri('adlcp'),
                        'masteryscore'
                    );

                    if ($launchDatas->length > 0) {
                        $launchData = $launchDatas->item(0)->nodeValue;
                    } else {
                        $launchData = '';
                    }

                    if ($masteryScores->length > 0) {
                        $masteryScore = intval($masteryScores->item(0)->nodeValue);
                    } else {
                        $masteryScore = -1;
                    }

                    foreach ($scoResources as $scoResource) {
                        $identifier = $scoResource->attributes->getNamedItem('identifier');
                        $href = $scoResource->attributes->getNamedItem('href');
                        $scormType = $scoResource->attributes->getNamedItemNS(
                            $scoResource->lookupNamespaceUri('adlcp'),
                            'scormtype'
                        );

                        // For compatibility with Raptivity scorm 1.2 package
                        if (is_null($scormType)) {
                            $scormType = $scoResource->attributes->getNamedItemNS(
                                $scoResource->lookupNamespaceUri('adlcp'),
                                'scormType'
                            );
                        }

                        if (!is_null($identifier)
                            && !is_null($scormType)
                            && !is_null($href)
                            && $identifier->nodeValue === $identifierRef
                            && $scormType->nodeValue === 'sco') {

                            $scoUrl = $href->nodeValue;

                            $scorm = new Scorm12();
                            $scorm->setName($title);
                            $scorm->setEntryUrl($scoUrl);
                            $scorm->setLaunchData($launchData);
                            $scorm->setMasteryScore($masteryScore);
                            $scorm->setHashName($hashName);
                            $resources[] = $scorm;
                            break;
                        }
                    }
                }
            }
        } else {

            foreach ($scoResources as $scoResource) {
                $identifier = $scoResource->attributes->getNamedItem('identifier');
                $href = $scoResource->attributes->getNamedItem('href');
                $scormType = $scoResource->attributes->getNamedItemNS(
                    $scoResource->lookupNamespaceUri('adlcp'),
                    'scormtype'
                );

                // For compatibility with Raptivity scorm 1.2 package
                if (is_null($scormType)) {
                    $scormType = $scoResource->attributes->getNamedItemNS(
                        $scoResource->lookupNamespaceUri('adlcp'),
                        'scormType'
                    );
                }

                if (!is_null($identifier)
                    && !is_null($scormType)
                    && !is_null($href)
                    && ($scormType->nodeValue === 'sco')) {

                    $scoUrl = $href->nodeValue;

                    $scorm = new Scorm12();
                    $scorm->setName('no_title');
                    $scorm->setEntryUrl($scoUrl);
                    $scorm->setHashName($hashName);
                    $resources[] = $scorm;
                }
            }
        }

        $zip->close();

        if (count($resources) === 0) {
            throw new InvalidScorm12ArchiveException('no_sco_found_message');
        }

        return $resources;
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param $dir The path to the directory to delete.
     */
    private function deleteFiles($dirPath)
    {
        foreach (glob($dirPath . '/*') as $content) {

            if (is_dir($content)) {
                $this->deleteFiles($content);
            } else {
                unlink($content);
            }
        }
        rmdir($dirPath);
    }
}