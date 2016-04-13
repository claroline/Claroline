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
use Claroline\CoreBundle\Entity\Calendar\Year;

/**
 * @DI\Service("claroline.manager.calendar.year_manager")
 */
class YearManager
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
        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Calendar\Year');
    }

    public function create(Year $year)
    {
        $this->om->persist($year);
        $this->om->flush();
    }

    public function delete(Year $year)
    {
        $this->om->remove($year);
        $this->om->flush();
    }

    public function edit(Year $year)
    {
        $this->om->persist($year);
        $this->om->flush();
    }

    public function resize($begin, $end)
    {
        //do not forget to trim / add the last/first period or stuff like this
    }

    //repositories method

    public function getAll()
    {
        return $this->repo->findAll();
    }
}
