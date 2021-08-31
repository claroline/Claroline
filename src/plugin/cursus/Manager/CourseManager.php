<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Manager;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Claroline\CursusBundle\Entity\Session;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CourseManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var TranslatorInterface */
    private $translator;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformManager */
    private $platformManager;
    /** @var TemplateManager */
    private $templateManager;
    /** @var SessionManager */
    private $sessionManager;

    private $courseUserRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        ObjectManager $om,
        PlatformManager $platformManager,
        TemplateManager $templateManager,
        SessionManager $sessionManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->translator = $translator;
        $this->platformManager = $platformManager;
        $this->templateManager = $templateManager;
        $this->sessionManager = $sessionManager;

        $this->courseUserRepo = $this->om->getRepository(CourseUser::class);
    }

    public function generateFromTemplate(Course $course, string $locale)
    {
        $placeholders = [
            'course_name' => $course->getName(),
            'course_code' => $course->getCode(),
            'course_description' => $course->getDescription(),
            'course_poster' => $course->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$course->getPoster().'" style="max-width: 100%;"/>' : '',
            'course_default_duration' => $course->getDefaultSessionDuration(),
            'course_public_registration' => $this->translator->trans($course->getPublicRegistration() ? 'yes' : 'no', [], 'platform'),
            'course_max_users' => $course->getMaxUsers(),
        ];

        $content = $this->templateManager->getTemplate('training_course', $placeholders, $locale);

        // append all available sessions to the export
        foreach ($course->getSessions() as $session) {
            if (!$session->isTerminated()) {
                $content .= "<div style='page-break-before: always'>{$this->sessionManager->generateFromTemplate($session, $locale)}</div>";
            }
        }

        return $content;
    }

    public function addUsers(Course $course, array $users): array
    {
        $results = [];

        $registrationDate = new \DateTime();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $courseUser = $this->courseUserRepo->findOneBy(['course' => $course, 'user' => $user]);

            if (empty($courseUser)) {
                $courseUser = new CourseUser();
                $courseUser->setCourse($course);
                $courseUser->setUser($user);
                $courseUser->setType(AbstractRegistration::LEARNER);
                $courseUser->setDate($registrationDate);

                $this->om->persist($courseUser);

                $results[] = $courseUser;
            }
        }

        /*if ($course->getRegistrationMail()) {
            $this->sendSessionInvitation($session, array_map(function (SessionUser $sessionUser) {
                return $sessionUser->getUser();
            }, $results), AbstractRegistration::LEARNER === $type);
        }*/

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param CourseUser[] $courseUsers
     */
    public function removeUsers(array $courseUsers)
    {
        foreach ($courseUsers as $courseUser) {
            $this->om->remove($courseUser);
        }

        $this->om->flush();
    }

    /**
     * @param CourseUser[] $courseUsers
     */
    public function moveUsers(Session $targetSession, array $courseUsers)
    {
        $this->om->startFlushSuite();

        // unregister users from course pending list
        $this->removeUsers($courseUsers);

        // register to the new session
        $this->sessionManager->addUsers($targetSession, array_map(function (CourseUser $sessionUser) {
            return $sessionUser->getUser();
        }, $courseUsers), AbstractRegistration::LEARNER, true);

        $this->om->endFlushSuite();
    }
}
