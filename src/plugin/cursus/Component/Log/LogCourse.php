<?php

namespace Claroline\CursusBundle\Component\Log\Operational;

use Claroline\CursusBundle\Entity\Course;
use Claroline\LogBundle\Component\Log\AbstractOperationalLog;

class LogCourse extends AbstractOperationalLog
{
    public static function getName(): string
    {
        return 'training_course';
    }

    protected static function getEntityClass(): string
    {
        return Course::class;
    }
}
