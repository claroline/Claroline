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
use Claroline\CoreBundle\Entity\Calendar\Period;

/**
 * @DI\Service("claroline.manager.calendar.period_manager")
 */
class PeriodManager
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
        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Calendar\Period');
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

    public function resize($begin, $end)
    {
        //you may want to trim timeslots if you reduce it
        //so let's do stuff here
    }

    //repositories method

    public function getAll()
    {
        return $this->repo->findAll();
    }
}
