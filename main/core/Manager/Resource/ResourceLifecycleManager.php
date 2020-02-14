<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\UserEvaluationEvent;

/**
 * Centralizes events dispatched for resources integration.
 *
 * @todo finish me
 */
class ResourceLifecycleManager
{
    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var ObjectManager */
    private $om;

    /**
     * ResourceLifecycleManager constructor.
     *
     * @param StrictDispatcher $eventDispatcher
     * @param ObjectManager    $om
     */
    public function __construct(
        StrictDispatcher $eventDispatcher,
        ObjectManager $om)
    {
        $this->dispatcher = $eventDispatcher;
        $this->om = $om;
    }

    public function load(ResourceNode $resourceNode)
    {
        /** @var LoadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('load', $resourceNode),
            LoadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function create(ResourceNode $resourceNode)
    {
        /** @var CreateResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('create', $resourceNode),
            CreateResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function edit(ResourceNode $resourceNode)
    {
        // fixme : wrong event. Use crud instead ?

        /** @var CopyResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('edit', $resourceNode),
            CopyResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function copy($originalResource, $copiedResource)
    {
        /** @var CopyResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('copy', $copiedResource->getResourceNode()),
            CopyResourceEvent::class,
            [$originalResource, $copiedResource]
        );

        return $event;
    }

    /**
     * @param ResourceNode $resourceNode
     *
     * @return DownloadResourceEvent
     */
    public function export(ResourceNode $resourceNode)
    {
        /** @var DownloadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('export', $resourceNode), // old download
            DownloadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function delete(ResourceNode $resourceNode)
    {
        /** @var DeleteResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('delete', $resourceNode), // old download
            DeleteResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function evaluate(ResourceUserEvaluation $resourceUserEvaluation)
    {
        /** @var UserEvaluationEvent $event */
        $event = $this->dispatcher->dispatch(
            'evaluate', // old : resource_evaluation
            UserEvaluationEvent::class,
            [$resourceUserEvaluation]
        );

        return $event;
    }

    /**
     * Generates the names for dispatched events.
     *
     * @param string       $prefix
     * @param ResourceNode $resourceNode
     *
     * @return string
     */
    private static function eventName($prefix, ResourceNode $resourceNode)
    {
        return 'resource.'.$resourceNode->getResourceType()->getName().'.'.$prefix;
    }

    /**
     * Returns the resource linked to a node.
     *
     * @param ResourceNode $resourceNode
     *
     * @return AbstractResource
     */
    private function getResourceFromNode(ResourceNode $resourceNode)
    {
        /** @var AbstractResource $resource */
        $resource = $this->om
            ->getRepository($resourceNode->getClass())
            ->findOneBy(['resourceNode' => $resourceNode]);

        return $resource;
    }
}
