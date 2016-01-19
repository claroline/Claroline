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
use Claroline\CoreBundle\Entity\Calendar\ScheduleTemplate;

/**
 * @DI\Service("claroline.manager.calendar.schedule_template_manager")
 */
class ScheduleTemplateManager 
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
        $this->repo = $this->om->getRepository('ClarolineCoreBundle:Calendar\ScheduleTemplate');
    }

    public function create(ScheduleTemplate $schedule)
    {
        $this->om->persist($schedule);
        $this->om->flush();
    }

    public function delete(ScheduleTemplate $schedule)
    {
        $this->om->remove($schedule);
        $this->om->flush();
    }

    public function edit(ScheduleTemplate $schedule)
    {
        $this->om->persist($schedule);
        $this->om->flush();
    }

    public function getAll()
    {
        return $this->repo->findAll();
    }
}
