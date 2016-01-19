<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Calendar\Period;

/**
 * @DI\Service("claroline.manager.period_manager")
 */
class PeriodManager 
{

    /**
     * @DI\InjectParams({
     *      "om"   = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function create(Period $period)
    {
        $this->om->persist($period);
        $this->om->flush();
    }

    public function delete(Period $period)
    {
        $this->om->remove($period);
        $this->om->flush();
    }

    public function edit(Period $period)
    {
        $this->om->persist($period);
        $this->om->flush();
    }
}
