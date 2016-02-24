<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Library\Testing;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @service("claroline.library.testing.cursuspersister")
 */
class CursusPersister
{
    private $om;

    /**
     * @InjectParams({
     *     "om" = @Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function cursus($name)
    {
        $cursus = new Cursus();
        $cursus->setTitle($name);
        $cursus->setCode($name);
        $cursus->setDescription($name);
        $cursus->setCursusOrder(0);
        $this->om->persist($cursus);

        return $cursus;
    }

    public function course($name)
    {
        $course = new Course();
        $course->setTitle($name);
        $course->setCode($name);
        $course->setDescription($name);
        $this->om->persist($course);

        return $course;
    }

    public function session($name, Course $course)
    {
        $now = new \DateTime();

        $session = new CourseSession();
        $session->setName($name);
        $session->setCourse($course);
        $session->setCreationDate($now);
        $this->om->persist($session);

        return $session;
    }
}
