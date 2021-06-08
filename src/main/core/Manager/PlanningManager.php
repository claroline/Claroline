<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Planning\Planning;
use Claroline\CoreBundle\Event\Planning\PlanObjectEvent;
use Claroline\CoreBundle\Event\Planning\UnplanObjectEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlanningManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
    }

    public function addToPlanning(AbstractPlanned $planned, IdentifiableInterface $object)
    {
        $planning = $this->om->getRepository(Planning::class)->findOneBy([
            'objectId' => $object->getUuid(),
        ]);

        if (empty($planning)) {
            $planning = new Planning();
            $planning->setObjectClass(get_class($object));
            $planning->setObjectId($object->getUuid());

            $this->om->persist($planning);
        }

        $planning->addPlannedObject($planned->getPlannedObject());
        $this->om->persist($planned);

        // dispatch event before flush to allow subscriber to cancel it if needed
        // this should have been done at validation level, but it's not possible for now (no event to listen to)
        $this->eventDispatcher->dispatch(new PlanObjectEvent($planned, $object));

        $this->om->flush();
    }

    public function removeFromPlanning(AbstractPlanned $planned, IdentifiableInterface $object)
    {
        $planning = $this->om->getRepository(Planning::class)->findOneBy([
            'objectId' => $object->getUuid(),
        ]);

        if ($planning) {
            $planning->removePlannedObject($planned->getPlannedObject());
        }

        $this->eventDispatcher->dispatch(new UnplanObjectEvent($planned, $object));

        $this->om->flush();
    }
}
