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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusGroup;
use Claroline\CursusBundle\Entity\CursusUser;
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

    public function cursus(
        $name,
        Cursus $parent = null,
        Course $course = null,
        $order = 0,
        $blocking = false
    ) {
        $cursus = new Cursus();
        $cursus->setTitle($name);
        $cursus->setCode($name);
        $cursus->setDescription($name);
        $cursus->setParent($parent);
        $cursus->setCourse($course);
        $cursus->setCursusOrder($order);
        $cursus->setBlocking($blocking);
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

    public function session($name, Course $course, $status = 0)
    {
        $now = new \DateTime();

        $session = new CourseSession();
        $session->setName($name);
        $session->setCourse($course);
        $session->setCreationDate($now);
        $session->setSessionStatus($status);
        $this->om->persist($session);

        return $session;
    }

    public function courseQueue(Course $course, User $user)
    {
        $now = new \DateTime();
        $status = 0;
        $validators = $course->getValidators();

        if ($course->getUserValidation()) {
            $status += CourseRegistrationQueue::WAITING_USER;
        }

        if ($course->getOrganizationValidation()) {
            $status += CourseRegistrationQueue::WAITING_ORGANIZATION;
        }

        if (count($validators) > 0) {
            $status += CourseRegistrationQueue::WAITING_VALIDATOR;
        } elseif ($course->getRegistrationValidation()) {
            $status += CourseRegistrationQueue::WAITING;
        }
        $courseQueue = new CourseRegistrationQueue();
        $courseQueue->setUser($user);
        $courseQueue->setCourse($course);
        $courseQueue->setApplicationDate($now);
        $courseQueue->setStatus($status);
        $this->om->persist($courseQueue);

        return $courseQueue;
    }

    public function sessionQueue(CourseSession $session, User $user)
    {
        $now = new \DateTime();
        $status = 0;
        $validators = $session->getValidators();

        if ($session->getUserValidation()) {
            $status += CourseRegistrationQueue::WAITING_USER;
        }

        if ($session->getOrganizationValidation()) {
            $status += CourseRegistrationQueue::WAITING_ORGANIZATION;
        }

        if (count($validators) > 0) {
            $status += CourseRegistrationQueue::WAITING_VALIDATOR;
        } elseif ($session->getRegistrationValidation()) {
            $status += CourseRegistrationQueue::WAITING;
        }
        $sessionQueue = new CourseSessionRegistrationQueue();
        $sessionQueue->setUser($user);
        $sessionQueue->setSession($session);
        $sessionQueue->setApplicationDate($now);
        $sessionQueue->setStatus($status);
        $this->om->persist($sessionQueue);

        return $sessionQueue;
    }

    public function cursusGroup(Group $group, Cursus $cursus, $type = 0)
    {
        $now = new \DateTime();
        $cursusGroup = new CursusGroup();
        $cursusGroup->setGroup($group);
        $cursusGroup->setCursus($cursus);
        $cursusGroup->setGroupType($type);
        $cursusGroup->setRegistrationDate($now);
        $this->om->persist($cursusGroup);

        return $cursusGroup;
    }

    public function cursusUser(User $user, Cursus $cursus, $type = 0)
    {
        $now = new \DateTime();
        $cursusUser = new CursusUser();
        $cursusUser->setUser($user);
        $cursusUser->setCursus($cursus);
        $cursusUser->setUserType($type);
        $cursusUser->setRegistrationDate($now);
        $this->om->persist($cursusUser);

        return $cursusUser;
    }
}
