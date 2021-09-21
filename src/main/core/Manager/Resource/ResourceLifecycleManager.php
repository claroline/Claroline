<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;

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

    public function export(ResourceNode $resourceNode)
    {
        /** @var DownloadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('export', $resourceNode),
            DownloadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function delete(ResourceNode $resourceNode, bool $soft = true)
    {
        /** @var DeleteResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('delete', $resourceNode),
            DeleteResourceEvent::class,
            [$this->getResourceFromNode($resourceNode), $soft]
        );

        return $event;
    }

    public function evaluate(ResourceUserEvaluation $resourceUserEvaluation, ResourceEvaluation $attempt)
    {
        /** @var ResourceEvaluationEvent $event */
        $event = $this->dispatcher->dispatch(
            'evaluate', // old : resource_evaluation
            ResourceEvaluationEvent::class,
            [$resourceUserEvaluation, $attempt]
        );

        return $event;
    }

    /**
     * Generates the names for dispatched events.
     *
     * @param string $prefix
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
