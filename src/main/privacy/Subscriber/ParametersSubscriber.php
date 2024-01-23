<?php

namespace Claroline\PrivacyBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Tool\AbstractToolSubscriber;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParametersSubscriber extends AbstractToolSubscriber implements EventSubscriberInterface
{
    private SerializerProvider $serializer;
    private PrivacyManager $privacyManager;

    public function __construct(
        SerializerProvider $serializer,
        PrivacyManager $privacyManager
    ) {
        $this->serializer = $serializer;
        $this->privacyManager = $privacyManager;
    }

    protected static function supportsTool(string $toolName): bool
    {
        return 'privacy' === $toolName;
    }

    protected function onOpen(OpenToolEvent $event): void
    {
        $privacyParameters = $this->privacyManager->getParameters();
        $serializedParameters = $this->serializer->serialize($privacyParameters);
        $event->addResponse([
            'privacy' => $serializedParameters,
        ]);
    }
}
