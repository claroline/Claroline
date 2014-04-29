<?php

namespace Claroline\ScormBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Persistence\ObjectManager;
//use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScormInfo;
use Claroline\ScormBundle\Form\ScormType;
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
class ScormListener
{
    private $container;
    private $formFactory;
    private $om;
    private $request;
    private $router;
    private $securityContext;
    private $templating;
    private $translator;
    private $uploadPath;

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
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->translator = $translator;
        $this->uploadPath = $this->container
            ->getParameter('kernel.root_dir') . '/../web/uploads/webresource/';
    }

    /**
     * @DI\Observe("create_form_claroline_scorm")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(new ScormType(), new Scorm());
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_scorm'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_scorm")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $form = $this->formFactory->create(new ScormType(), new Scorm());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $tmpFile = $form->get('file')->getData();

            if ($this->isScormArchive($tmpFile)) {
                $scormResources = $this->createScormResource($tmpFile);
                $event->setResources($scormResources);
                $event->stopPropagation();

                return;
            } else {
                $errorMsg = $this->translator->trans(
                    'invalid_scorm_archive_message',
                    array(),
                    'resource'
                );
                $form->addError(new FormError($errorMsg));
            }
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
     * @DI\Observe("open_claroline_scorm")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $scorm = $event->getResource();
        $hashName = $scorm->getHashName();
        $relativePath = $hashName
            . $ds
            . $scorm->getEntryUrl();
        $route = $this->router->getContext()->getBaseUrl();
        $fp = preg_replace('"/web/app_dev.php$"', "/web/uploads/webresource/$relativePath", $route);

        $user = $this->securityContext->getToken()->getUser();
        $scormInfo = $this->om->getRepository('ClarolineScormBundle:ScormInfo')
            ->findOneBy(array('user' => $user->getId(), 'scorm' => $scorm->getId()));

        if (is_null($scormInfo)) {
            $scormInfo = new ScormInfo();
            $scormInfo->setUser($user);
            $scormInfo->setScorm($scorm);
            $scormInfo->setScoreRaw(-1);
            $scormInfo->setScoreMax(-1);
            $scormInfo->setScoreMin(-1);
            $scormInfo->setLessonStatus("not attempted");
            $scormInfo->setSuspendData("");
            $scormInfo->setEntry("ab-initio");
            $scormInfo->setLessonLocation("");
            $scormInfo->setCredit("no-credit");
            $scormInfo->setTotalTime(0);
            $scormInfo->setSessionTime(0);
            $scormInfo->setLessonMode("normal");
            $scormInfo->setExitMode("");
        }
        $content = $this->templating->render(
            'ClarolineScormBundle::scorm.html.twig',
            array(
                'resource' => $event->getResource(),
                '_resource' => $event->getResource(),
                'scormInfo' => $scormInfo,
                'scormUrl' => $fp,
                'workspace' => $scorm->getResourceNode()->getWorkspace()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_scorm")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $webResourcesPath = $this->uploadPath . $event->getResource()->getHashName();

        if (file_exists($webResourcesPath)) {
            $this->deleteFiles($webResourcesPath);
        }
        $this->om->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_scorm")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new Scorm();
        $copy->setHashName($resource->getHashName());
        $copy->setLaunchData($resource->getLaunchData());
        $copy->setMasteryScore($resource->getMasteryScore());
        $copy->setEntryUrl($resource->getEntryUrl());
        $copy->setName($resource->getName());
        $event->setCopy($copy);
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

        return $file->getClientMimeType() === 'application/zip'
            && $zip->open($file)
            && $zip->getStream("imsmanifest.xml");
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
        $destinationDir = $this->uploadPath . $hashName;

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

        $items = $dom->getElementsByTagName('item');
        $scoResources = $dom->getElementsByTagName('resource');

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

                        $scorm = new Scorm();
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
        $zip->close();

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