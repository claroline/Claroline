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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Manager\ScormManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class ScormListener
{
    /** @var string */
    private $filesDir;
    /** @var Filesystem */
    private $fileSystem;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;
    /** @var ScormManager */
    private $scormManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TwigEngine */
    private $templating;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var string */
    private $uploadDir;

    private $scormResourceRepo;
    private $scoTrackingRepo;

    /**
     * @DI\InjectParams({
     *     "filesDir"            = @DI\Inject("%claroline.param.files_directory%"),
     *     "fileSystem"          = @DI\Inject("filesystem"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "scormManager"        = @DI\Inject("claroline.manager.scorm_manager"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "uploadDir"           = @DI\Inject("%claroline.param.uploads_directory%")
     * })
     *
     * @param string                    $filesDir
     * @param Filesystem                $fileSystem
     * @param ObjectManager             $om
     * @param ScormManager              $scormManager
     * @param SerializerProvider        $serializer
     * @param TwigEngine                $templating
     * @param TokenStorageInterface     $tokenStorage
     * @param ResourceEvaluationManager $resourceEvalManager
     * @param string                    $uploadDir
     */
    public function __construct(
        $filesDir,
        Filesystem $fileSystem,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        ScormManager $scormManager,
        SerializerProvider $serializer,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        $uploadDir
    ) {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->scormManager = $scormManager;
        $this->serializer = $serializer;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->uploadDir = $uploadDir;

        $this->scormResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm');
        $this->scoTrackingRepo = $om->getRepository('ClarolineScormBundle:ScoTracking');
    }

    /**
     * @DI\Observe("open_claroline_scorm")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $scorm = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            $user = null;
        }
        $content = $this->templating->render(
            'ClarolineScormBundle::scorm.html.twig', [
                '_resource' => $scorm,
                'userEvaluation' => is_null($user) ?
                    null :
                    $this->resourceEvalManager->getResourceUserEvaluation($scorm->getResourceNode(), $user),
                'trackings' => $this->scormManager->generateScosTrackings($scorm->getRootScos(), $user),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("load_claroline_scorm")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $scorm = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            $user = null;
        }
        $event->setAdditionalData([
            'scorm' => $this->serializer->serialize($scorm),
            'evaluation' => is_null($user) ?
                null :
                $this->serializer->serialize(
                    $this->resourceEvalManager->getResourceUserEvaluation($scorm->getResourceNode(), $user)
                ),
            'trackings' => $this->scormManager->generateScosTrackings($scorm->getRootScos(), $user),
        ]);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_scorm")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $scorm = $event->getResource();
        $workspace = $scorm->getResourceNode()->getWorkspace();
        $hashName = $scorm->getHashName();

        $nbScorm = (int) ($this->scormResourceRepo->findNbScormWithSameSource($hashName, $workspace));

        if (1 === $nbScorm) {
            $scormArchiveFile = $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName;
            $scormResourcesPath = $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName;

            if (file_exists($scormArchiveFile)) {
                $event->setFiles([$scormArchiveFile]);
            }
            if (file_exists($scormResourcesPath)) {
                try {
                    $this->deleteFiles($scormResourcesPath);
                } catch (\Exception $e) {
                }
            }
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
        $workspace = $resource->getResourceNode()->getWorkspace();
        $newWorkspace = $event->getCopiedNode()->getWorkspace();
        $copy = new Scorm();
        $hashName = $resource->getHashName();
        $copy->setHashName($hashName);
        $copy->setName($resource->getName());
        $copy->setVersion($resource->getVersion());
        $copy->setRatio($resource->getRatio());
        $this->om->persist($copy);

        $scos = $resource->getScos();

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $this->copySco($sco, $copy);
            }
        }
        if ($workspace->getId() !== $newWorkspace->getId()) {
            $ds = DIRECTORY_SEPARATOR;
            /* Copies archive file & unzipped files */
            if ($this->fileSystem->exists($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName)) {
                $this->fileSystem->copy(
                    $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName,
                    $this->filesDir.$ds.'scorm'.$ds.$newWorkspace->getUuid().$ds.$hashName
                );
            }
            if ($this->fileSystem->exists($this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName)) {
                $this->fileSystem->mirror(
                    $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName,
                    $this->uploadDir.$ds.'scorm'.$ds.$newWorkspace->getUuid().$ds.$hashName
                );
            }
        }

        $event->setCopy($copy);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_claroline_scorm")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $scorm = $event->getResource();
        $workspace = $scorm->getResourceNode()->getWorkspace();
        $event->setItem($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$scorm->getHashName());
        $event->setExtension('zip');
        $event->stopPropagation();
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param $dirPath The path to the directory to delete
     */
    private function deleteFiles($dirPath)
    {
        foreach (glob($dirPath.DIRECTORY_SEPARATOR.'{*,.[!.]*,..?*}', GLOB_BRACE) as $content) {
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
     * @param Sco   $sco
     * @param Scorm $resource
     * @param Sco   $scoParent
     */
    private function copySco(Sco $sco, Scorm $resource, Sco $scoParent = null)
    {
        $scoCopy = new Sco();
        $scoCopy->setScorm($resource);
        $scoCopy->setScoParent($scoParent);

        $scoCopy->setEntryUrl($sco->getEntryUrl());
        $scoCopy->setIdentifier($sco->getIdentifier());
        $scoCopy->setTitle($sco->getTitle());
        $scoCopy->setVisible($sco->isVisible());
        $scoCopy->setParameters($sco->getParameters());
        $scoCopy->setLaunchData($sco->getLaunchData());
        $scoCopy->setMaxTimeAllowed($sco->getMaxTimeAllowed());
        $scoCopy->setTimeLimitAction($sco->getTimeLimitAction());
        $scoCopy->setBlock($sco->isBlock());
        $scoCopy->setScoreToPassInt($sco->getScoreToPassInt());
        $scoCopy->setScoreToPassDecimal($sco->getScoreToPassDecimal());
        $scoCopy->setCompletionThreshold($sco->getCompletionThreshold());
        $scoCopy->setPrerequisites($sco->getPrerequisites());
        $this->om->persist($scoCopy);

        foreach ($sco->getScoChildren() as $scoChild) {
            $this->copySco($scoChild, $resource, $scoCopy);
        }
    }
}
