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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\CourseUser;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class CourseManager
{
    private TranslatorInterface $translator;
    private ObjectManager $om;
    private Crud $crud;
    private SerializerProvider $serializer;
    private FinderProvider $finder;
    private PlatformManager $platformManager;
    private TemplateManager $templateManager;
    private SessionManager $sessionManager;

    private $courseUserRepo;

    public function __construct(
        TranslatorInterface $translator,
        ObjectManager $om,
        Crud $crud,
        SerializerProvider $serializer,
        FinderProvider $finder,
        PlatformManager $platformManager,
        TemplateManager $templateManager,
        SessionManager $sessionManager
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->crud = $crud;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->platformManager = $platformManager;
        $this->templateManager = $templateManager;
        $this->sessionManager = $sessionManager;

        $this->courseUserRepo = $this->om->getRepository(CourseUser::class);
    }

    public function generateFromTemplate(Course $course, string $locale): string
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

        return $this->templateManager->getTemplate('training_course', $placeholders, $locale);
    }

    public function getRegistrations(Course $course, User $user): array
    {
        $userRegistrations = $this->finder->fetch(SessionUser::class, [
            'user' => $user->getUuid(),
            'course' => $course->getUuid(),
        ]);

        $groupRegistrations = $this->finder->fetch(SessionGroup::class, [
            'user' => $user->getUuid(),
            'course' => $course->getUuid(),
        ]);

        $courseRegistrations = $this->finder->fetch(CourseUser::class, [
            'user' => $user->getUuid(),
            'course' => $course->getUuid(),
        ]);

        return [
            'users' => array_map(function (SessionUser $sessionUser) {
                return $this->serializer->serialize($sessionUser);
            }, $userRegistrations),
            'groups' => array_map(function (SessionGroup $sessionGroup) use (&$registeredSessions) {
                return $this->serializer->serialize($sessionGroup);
            }, $groupRegistrations),
            'pending' => array_map(function (CourseUser $courseUser) {
                return $this->serializer->serialize($courseUser);
            }, $courseRegistrations),
        ];
    }

    public function addUsers(Course $course, array $users, array $registrationData = []): array
    {
        $results = [];

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $courseUser = $this->courseUserRepo->findOneBy(['course' => $course, 'user' => $user]);

            if (empty($courseUser)) {
                $courseUser = new CourseUser();
                $courseUser->setCourse($course);
                $courseUser->setUser($user);

                $this->crud->create($courseUser, [
                    'type' => AbstractRegistration::LEARNER,
                    'data' => !empty($registrationData[$user->getUuid()]) ? $registrationData[$user->getUuid()] : [],
                ], [Crud::THROW_EXCEPTION]);
            }

            $results[] = $courseUser;
        }

        $this->om->endFlushSuite();

        return $results;
    }

    /**
     * @param CourseUser[] $courseUsers
     */
    public function moveUsers(Session $targetSession, array $courseUsers): void
    {
        $this->om->startFlushSuite();

        // unregister users from course pending list
        $this->crud->deleteBulk($courseUsers);

        // register to the new session
        $registrationData = [];
        $users = array_map(function (CourseUser $courseUser) use (&$registrationData) {
            $serialized = $this->serializer->serialize($courseUser);
            if ($serialized['data']) {
                $registrationData[$courseUser->getUser()->getUuid()] = $serialized['data'];
            }

            return $courseUser->getUser();
        }, $courseUsers);

        $this->sessionManager->addUsers($targetSession, $users, AbstractRegistration::LEARNER, true, $registrationData);

        $this->om->endFlushSuite();
    }

    /**
     * @param SessionUser[] $sessionUsers
     */
    public function moveToPending(Course $course, array $sessionUsers): void
    {
        if (!empty($sessionUsers)) {
            $session = $sessionUsers[0]->getSession();

            if (!empty($session) && !empty($course)) {
                $this->om->startFlushSuite();

                // remove users from session
                $this->crud->deleteBulk($sessionUsers);

                // add users to the pending list of the course
                $registrationData = [];
                $users = array_map(function (SessionUser $sessionUser) use (&$registrationData) {
                    $serialized = $this->serializer->serialize($sessionUser);
                    if ($serialized['data']) {
                        $registrationData[$sessionUser->getUser()->getUuid()] = $serialized['data'];
                    }

                    return $sessionUser->getUser();
                }, $sessionUsers);

                $this->addUsers($course, $users, $registrationData);

                $this->om->endFlushSuite();
            }
        }
    }
}
