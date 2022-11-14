<?php

namespace Claroline\CoreBundle\API\Serializer\Log;

use Claroline\AppBundle\API\Options;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LogSerializer
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    /** @var ResourceNodeSerializer */
    private $resourceSerializer;

    /**
     * LogSerializer constructor.
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        UserSerializer $userSerializer,
        WorkspaceSerializer $workspaceSerializer,
        ResourceNodeSerializer $resourceSerializer
    ) {
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
        $this->userSerializer = $userSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->resourceSerializer = $resourceSerializer;
    }

    public function getClass()
    {
        return Log::class;
    }

    public function getName()
    {
        return 'log';
    }

    /**
     * Serializes a Log entity.
     *
     * @return array
     */
    public function serialize(Log $log, array $options = [])
    {
        $doer = null;
        if (!is_null($log->getDoer())) {
            $doer = $this->userSerializer->serialize($log->getDoer(), [Options::SERIALIZE_MINIMAL]);
        }

        $workspace = null;
        if (!is_null($log->getWorkspace())) {
            $workspace = $this->workspaceSerializer->serialize($log->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
        }

        $resourceNode = null;
        if (!is_null($log->getResourceNode())) {
            $resourceNode = $this->resourceSerializer->serialize($log->getResourceNode(), [Options::SERIALIZE_MINIMAL]);
        }

        $resourceType = null;
        if (!is_null($log->getResourceType())) {
            $resourceType = $log->getResourceType()->getName();
        }

        // Get log description (depending on log sentence rendering)
        $eventName = 'create_log_list_item_'.$log->getAction();
        if (!$this->dispatcher->hasListeners($eventName)) {
            $eventName = 'create_log_list_item';
        }

        /** @var LogCreateDelegateViewEvent $event */
        $event = $this->dispatcher->dispatch(new LogCreateDelegateViewEvent($log), $eventName);
        $description = $this->processContent($event->getResponseContent());

        $serialized = [
            'id' => $log->getId(),
            'action' => $this->translator->trans('log_'.$log->getAction().'_shortname', [], 'log'), // translation should be done in client
            'dateLog' => DateNormalizer::normalize($log->getDateLog()),
            'description' => $description,
            'doer' => $doer,
            'workspace' => $workspace,
            'resourceNode' => $resourceNode,
            'resourceType' => $resourceType,
        ];

        if (isset($options['details']) && $options['details']) {
            $this->addDetails($serialized, $log);
        }

        return $serialized;
    }

    private function addDetails(array &$serialized, Log $log)
    {
        // Get log details text (depending on plugin rendering)
        $eventName = 'create_log_details_'.$log->getAction();
        if (!$this->dispatcher->hasListeners($eventName)) {
            $eventName = 'create_log_details';
        }
        $event = new LogCreateDelegateViewEvent($log);
        $serialized['details'] = $log->getDetails();
        $serialized['detailedDescription'] = $this->processContent($this->dispatcher->dispatch($event, $eventName)->getResponseContent());
        $serialized['doerType'] = $log->getDoerType();
    }

    private function processContent($response)
    {
        return trim(preg_replace('/\s\s+/', ' ', $response));
    }
}
