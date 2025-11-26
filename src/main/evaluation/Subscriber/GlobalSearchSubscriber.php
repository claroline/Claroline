<?php

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GlobalSearchEvent;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalSearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GlobalSearchEvent::class => 'searchSequences',
        ];
    }

    public function searchSequences(GlobalSearchEvent $event): void
    {
        $search = $event->getSearch();
        $limit = $event->getLimit();

        if ($event->includeItems('sequence')) {
            $sequences = $this->om->getRepository(Sequence::class)->search($search, $limit);

            $event->addResults('sequence', array_map(function (Sequence $sequence) {
                return $this->serializer->serialize($sequence, [Options::SERIALIZE_MINIMAL]);
            }, $sequences));
        }
    }
}
