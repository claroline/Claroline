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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DownloadResourceEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Listener\NoHttpRequestException;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\ScormBundle\Entity\Scorm12Resource;
use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Form\ScormType;
use Claroline\ScormBundle\Listener\Exception\InvalidScormArchiveException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class Scorm12Listener
{
    private $container;
    // path to the Scorm archive file
    private $filePath;
    private $formFactory;
    private $httpKernel;
    private $om;
    private $request;
    private $router;
    private $scormResourceRepo;
    // path to the Scorm unzipped files
    private $scormResourcesPath;
    private $scorm12ScoTrackingRepo;
    private $templating;
    private $translator;
    private $resourceEvalManager;

    /**
     * @DI\InjectParams({
     *     "container"           = @DI\Inject("service_container"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "httpKernel"          = @DI\Inject("http_kernel"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"        = @DI\Inject("request_stack"),
     *     "router"              = @DI\Inject("router"),
     *     "templating"          = @DI\Inject("templating"),
     *     "translator"          = @DI\Inject("translator"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
     */
    public function __construct(
        ContainerInterface $container,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        TwigEngine $templating,
        TranslatorInterface $translator,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->container = $container;
        $this->filePath = $this->container
            ->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->scormResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm12Resource');
        $this->scormResourcesPath = $this->container
            ->getParameter('claroline.param.uploads_directory').'/scormresources/';
        $this->scorm12ScoTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm12ScoTracking');
        $this->templating = $templating;
        $this->translator = $translator;
        $this->resourceEvalManager = $resourceEvalManager;
    }

    /**
     * @DI\Observe("create_form_claroline_scorm_12")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->formFactory->create(
            new ScormType(),
            new Scorm12Resource()
        );
        $content = $this->templating->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'claroline_scorm_12',
            ]
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
        $form = $this->formFactory->create(
            new ScormType(),
            new Scorm12Resource()
        );
        $form->handleRequest($this->request);

        try {
            if ($form->isValid()) {
                $tmpFile = $form->get('file')->getData();

                if ($this->isScormArchive($tmpFile)) {
                    $scormResource = $this->container
                        ->get('claroline.manager.scorm_manager')
                        ->createScorm($tmpFile, $form->get('name')->getData(), '1.2');
                    $event->setResources([$scormResource]);
                    $event->stopPropagation();

                    return;
                }
            }
        } catch (InvalidScormArchiveException $e) {
            $msg = $e->getMessage();
            $errorMsg = $this->translator->trans(
                $msg,
                [],
                'resource'
            );
            $form->addError(new FormError($errorMsg));
        }
        $content = $this->templating->render(
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
     * @DI\Observe("open_claroline_scorm_12")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        if (!$this->request) {
            throw new NoHttpRequestException();
        }
        $scorm = $event->getResource();
        $params['_controller'] = 'ClarolineScormBundle:Scorm:renderScorm12Resource';
        $params['scormId'] = $scorm->getId();

        $subRequest = $this->request->duplicate(
            [],
            null,
            $params
        );
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

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
        $scormArchiveFile = $this->filePath.$hashName;
        $scormResourcesPath = $this->scormResourcesPath.$hashName;

        $nbScorm = (int) ($this->scormResourceRepo->getNbScormWithHashName($hashName));

        if (1 === $nbScorm) {
            if (file_exists($scormArchiveFile)) {
                $event->setFiles([$scormArchiveFile]);
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
        $copy = new Scorm12Resource();
        $copy->setHashName($resource->getHashName());
        $copy->setName($resource->getName());
        $this->om->persist($copy);

        $scos = $resource->getScos();

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $this->copySco($sco, $copy);
            }
        }

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
        $event->setItem($this->filePath.$event->getResource()->getHashName());
        $event->setExtension('zip');
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("generate_resource_user_evaluation_claroline_scorm_12")
     *
     * @param GenericDataEvent $event
     */
    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $this->resourceEvalManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-scorm_12-sco_result'],
            $startDate
        );

        if (count($logs) > 0) {
            $this->om->startFlushSuite();
            $tracking = $this->resourceEvalManager->getResourceUserEvaluation($node, $user);
            $tracking->setDate($logs[0]->getDateLog());
            $nbAttempts = 0;
            $nbOpenings = 0;
            $status = AbstractResourceEvaluation::STATUS_UNKNOWN;
            $score = null;
            $scoreMin = null;
            $scoreMax = null;
            $totalTime = null;
            $statusValues = [
                'not attempted' => 0,
                'unknown' => 1,
                'browsed' => 2,
                'incomplete' => 3,
                'failed' => 4,
                'completed' => 5,
                'passed' => 6,
            ];

            foreach ($logs as $log) {
                switch ($log->getAction()) {
                    case 'resource-read':
                        ++$nbOpenings;
                        break;
                    case 'resource-scorm_12-sco_result':
                        ++$nbAttempts;
                        $details = $log->getDetails();

                        if (isset($details['bestScore']) && (empty($score) || $details['bestScore'] > $score)) {
                            $score = $details['bestScore'];
                            $scoreMin = isset($details['scoreMin']) ? $details['scoreMin'] : null;
                            $scoreMax = isset($details['scoreMax']) ? $details['scoreMax'] : null;
                        }
                        if (isset($details['totalTime']) && (empty($totalTime) || $details['totalTime'] > $totalTime)) {
                            $totalTime = $details['totalTime'];
                        }
                        if (isset($details['bestStatus']) && ($statusValues[$details['bestStatus']] > $statusValues[$status])) {
                            $status = $details['bestStatus'];
                        }
                        break;
                }
            }
            switch ($status) {
                case 'passed':
                case 'failed':
                case 'completed':
                case 'incomplete':
                    break;
                case 'not attempted':
                    $status = AbstractResourceEvaluation::STATUS_NOT_ATTEMPTED;
                    break;
                case 'browsed':
                    $status = AbstractResourceEvaluation::STATUS_OPENED;
                    break;
                default:
                    $status = AbstractResourceEvaluation::STATUS_UNKNOWN;
            }
            $tracking->setStatus($status);
            $tracking->setScore($score);
            $tracking->setScoreMin($scoreMin);
            $tracking->setScoreMax($scoreMax);

            if ($totalTime) {
                $tracking->setDuration($totalTime / 100);
            }
            $tracking->setNbAttempts($nbAttempts);
            $tracking->setNbOpenings($nbOpenings);
            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }
        $event->stopPropagation();
    }

    /**
     * Checks if a UploadedFile is a zip archive that contains a
     * imsmanifest.xml file in its root directory.
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    private function isScormArchive(UploadedFile $file)
    {
        $zip = new \ZipArchive();
        $openValue = $zip->open($file);

        $isScormArchive = (true === $openValue)
            && $zip->getStream('imsmanifest.xml');

        if (!$isScormArchive) {
            throw new InvalidScormArchiveException('invalid_scorm_archive_message');
        }

        return true;
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param $dirPath The path to the directory to delete
     */
    private function deleteFiles($dirPath)
    {
        foreach (glob($dirPath.'/*') as $content) {
            if (is_dir($content)) {
                $this->deleteFiles($content);
            } else {
                unlink($content);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Copy given sco and its children.
     *
     * @param Scorm12Sco      $sco
     * @param Scorm12Resource $resource
     * @param Scorm12Sco      $scoParent
     */
    private function copySco(
        Scorm12Sco $sco,
        Scorm12Resource $resource,
        Scorm12Sco $scoParent = null
    ) {
        $scoCopy = new Scorm12Sco();
        $scoCopy->setScormResource($resource);
        $scoCopy->setScoParent($scoParent);
        $scoCopy->setEntryUrl($sco->getEntryUrl());
        $scoCopy->setIdentifier($sco->getIdentifier());
        $scoCopy->setIsBlock($sco->getIsBlock());
        $scoCopy->setLaunchData($sco->getLaunchData());
        $scoCopy->setMasteryScore($sco->getMasteryScore());
        $scoCopy->setMaxTimeAllowed($sco->getMaxTimeAllowed());
        $scoCopy->setParameters($sco->getParameters());
        $scoCopy->setPrerequisites($sco->getPrerequisites());
        $scoCopy->setTimeLimitAction($sco->getTimeLimitAction());
        $scoCopy->setTitle($sco->getTitle());
        $scoCopy->setVisible($sco->isVisible());
        $this->om->persist($scoCopy);

        foreach ($sco->getScoChildren() as $scoChild) {
            $this->copySco($scoChild, $resource, $scoCopy);
        }
    }
}
