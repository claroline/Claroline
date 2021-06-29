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
use Claroline\LogBundle\Messenger\Functional\Message\EvaluateResourceMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /** @var MessageBusInterface */
    private $messageBus;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        StrictDispatcher $eventDispatcher,
        ObjectManager $om,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator
    ) {
        $this->dispatcher = $eventDispatcher;
        $this->om = $om;
        $this->messageBus = $messageBus;
        $this->translator = $translator;
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
        $this->messageBus->dispatch(new EvaluateResourceMessage(
            $this->translator->trans(
                'resourceEvaluation',
                [
                    'userName' => $resourceUserEvaluation->getUser()->getUsername(),
                    'resourceName' => $resourceUserEvaluation->getResourceNode()->getName(),
                    'statusName' => $resourceUserEvaluation->getStatus(),
                    'userProgression' => $resourceUserEvaluation->getProgression().'/'.$resourceUserEvaluation->getProgressionMax(),
                    'durationTime' => $resourceUserEvaluation->getDuration(),
                ],
                'resource'
            ),
            $resourceUserEvaluation->getResourceNode()->getId(),
            $resourceUserEvaluation->getUser()->getId()
        ));
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
