<?php

namespace Claroline\CoreBundle\API\Serializer\Log;

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LogSerializer
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
    }

    public function getClass()
    {
        return Log::class;
    }

    public function getIdentifiers()
    {
        return ['id'];
    }

    /**
     * Serializes a Book reference entity.
     *
     * @param Log   $log
     * @param array $options
     *
     * @return array
     */
    public function serialize(Log $log, array $options = [])
    {
        $details = $log->getDetails();
        $doer = null;
        if (!is_null($log->getDoer())) {
            $doer = [
                'id' => $log->getDoer()->getId(),
                'name' => $details['doer']['firstName'].' '.$details['doer']['lastName'],
            ];
        }

        $workspace = null;
        if (!is_null($log->getWorkspace())) {
            $workspace = [
                'id' => $log->getWorkspace()->getId(),
                'name' => isset($details['workspace']['name']) ? $details['workspace']['name'] : $log->getWorkspace()->getName(),
            ];
        }

        $resourceNode = null;
        if (!is_null($log->getResourceNode())) {
            $resourceNode = [
                'id' => $log->getResourceNode()->getId(),
                'name' => isset($details['resource']['name']) ? $details['resource']['name'] : $log->getResourceNode()->getName(),
            ];
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
        $event = new LogCreateDelegateViewEvent($log);
        $description = $this->processContent($this->dispatcher->dispatch($eventName, $event)->getResponseContent());

        $serialized = [
            'id' => $log->getId(),
            'action' => $this->translator->trans('log_'.$log->getAction().'_shortname', [], 'log'), // translation should be done in client
            'dateLog' => $log->getDateLog()->format('Y-m-d\TH:i:s'),
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
        $serialized['detailedDescription'] = $this->processContent($this->dispatcher->dispatch($eventName, $event)->getResponseContent());
        $serialized['doerType'] = $log->getDoerType();
    }

    private function processContent($response)
    {
        return trim(preg_replace('/\s\s+/', ' ', $response));
    }
}
