<?php

namespace Claroline\CursusBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\InstallationBundle\Updater\Updater;

class Updater130024 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function postUpdate()
    {
        $this->om->startFlushSuite();

        /** @var Course[] $courses */
        $courses = $this->om->getRepository(Course::class)->findAll();
        foreach ($courses as $course) {
            $duration = $course->getDefaultSessionDays();
            $dayPart = floor($duration);
            $hourPart = $duration - $dayPart;

            if ($hourPart > 0.0) {
                $course->setDefaultSessionHours($hourPart * 24);
            }

            $course->setDefaultSessionDays($dayPart);
        }

        $this->om->endFlushSuite();
    }
}
