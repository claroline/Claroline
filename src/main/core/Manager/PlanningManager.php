<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Planning\Planning;

class PlanningManager
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
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

        $this->om->flush();
    }
}
