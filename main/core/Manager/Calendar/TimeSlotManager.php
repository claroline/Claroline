<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Calendar;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Calendar\TimeSlot;
use Claroline\CoreBundle\Entity\Calendar\Year;
use Claroline\CoreBundle\Entity\Calendar\Period;

/**
 * @DI\Service("claroline.manager.calendar.time_slot_manager")
 */
class TimeSlotManager
{
    private $om;
    private $repo;

    /**
     * @DI\InjectParams({
     *      "om"   = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Calendar\TimeSlot');
    }

    public function create(TimeSlot $timeSlot)
    {
        $this->om->persist($timeSlot);
        $this->om->flush();
    }

    public function delete(TimeSlot $timeSlot)
    {
        $this->om->remove($timeSlot);
        $this->om->flush();
    }

    public function edit(TimeSlot $timeSlot)
    {
        $this->om->persist($timeSlot);
        $this->om->flush();
    }

    //repositories method

    public function getAll()
    {
        return $this->repo->findAll();
    }

    public function getByYear(Year $year)
    {
        //get the years period and go for a getByPeriods !!!
    }

    public function getByPeriod(Period $period)
    {
        return $this->repo->findByPeriod($period);
    }

    public function buildFromTemplate($templateName)
    {
        //do complicated stuff here
    }
}
