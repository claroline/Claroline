<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("ujm.exo.subscription_manager")
 */
class SubscriptionManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Subscribes a Use to an Exercise
     */
    public function subscribe()
    {
        
    }
}
