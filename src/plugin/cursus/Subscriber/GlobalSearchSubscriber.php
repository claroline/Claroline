<?php

namespace Claroline\CursusBundle\Subscriber;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GlobalSearchEvent;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalSearchSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GlobalSearchEvent::class => 'searchTrainings',
        ];
    }

    public function searchTrainings(GlobalSearchEvent $event)
    {
        $search = $event->getSearch();
        $limit = $event->getLimit();

        if ($event->includeItems('training')) {
            $trainings = $this->om->getRepository(Course::class)->search($search, $limit);

            $event->addResults('training', array_map(function (Course $training) {
                return $this->serializer->serialize($training, [Options::SERIALIZE_MINIMAL]);
            }, $trainings));
        }
    }
}
