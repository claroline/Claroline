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
use Claroline\CoreBundle\Entity\Calendar\Leave;

/**
 * @DI\Service("claroline.manager.leave_manager")
 */
class LeaveManager 
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
}
