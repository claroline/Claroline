<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Event;

use Claroline\CursusBundle\Entity\CourseSession;
use Symfony\Component\EventDispatcher\Event;

class CreateCourseSessionEvent extends Event
{
    private $courseSession;

    public function __construct(CourseSession $courseSession)
    {
        $this->courseSession = $courseSession;
    }

    public function getCourseSession()
    {
        return $this->courseSession;
    }
}
