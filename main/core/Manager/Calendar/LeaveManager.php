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
use Claroline\CoreBundle\Entity\Calendar\Leave;
use Claroline\CoreBundle\Entity\Calendar\Year;

/**
 * @DI\Service("claroline.manager.calendar.leave_manager")
 */
class LeaveManager
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
        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Calendar\Leave');
    }

    public function create(Leave $leave)
    {
        $this->om->persist($leave);
        $this->om->flush();
    }

    public function delete(Leave $leave)
    {
        $this->om->remove($leave);
        $this->om->flush();
    }

    public function edit(Leave $leave)
    {
        $this->om->persist($leave);
        $this->om->flush();
    }

    //repositories method

    public function getAll()
    {
        return $this->repo->findAll();
    }

    public function import(Year $year)
    {
        //a file should be able to be imported with the leaves list because it's more practical !
    }
}
