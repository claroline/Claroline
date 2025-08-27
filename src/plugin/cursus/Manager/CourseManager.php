<?php

namespace Claroline\CursusBundle\Manager;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CourseManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly PlatformManager $platformManager,
        private readonly TemplateManager $templateManager
    ) {
    }

    public function open(Course $course): array
    {
        $defaultSession = null;

        // search for sessions in which the current user is registered
        $user = $this->tokenStorage->getToken()?->getUser();
        $registrations = [];
        if ($user instanceof User) {
            $registrations = $this->getRegistrations($user, $course);
        }

        $sessions = $this->om->getRepository(Session::class)->findAvailable($course);

        if (empty($defaultSession)) {
            // the current user is not registered to any session yet
            // get the default session to open
            switch ($course->getSessionOpening()) {
                case 'default':
                    if ($course->getDefaultSession() && !$course->getDefaultSession()->isCanceled()) {
                        $defaultSession = $course->getDefaultSession();
                    }
                    break;
                case 'first_available':
                    if (!empty($sessions)) {
                        $defaultSession = $sessions[0];
                    }
                    break;
            }
        }

        return [
            'course' => $this->serializer->serialize($course),
            'defaultSession' => $defaultSession ? $this->serializer->serialize($defaultSession) : null,
            'availableSessions' => array_map(function (Session $session) {
                return $this->serializer->serialize($session, [SerializerInterface::SERIALIZE_LIST]);
            }, $sessions),
            'registrations' => $registrations,
        ];
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
        ];

        return $this->templateManager->getTemplate('training_course', $placeholders, $locale);
    }

    public function getRegistrations(User $user, ?Course $course = null): array
    {
        $search = ['user' => $user];

        if ($course) {
            $search['course'] = $course;
        }

        $userRegistrations = $this->om->getRepository(SessionUser::class)->findBy($search);

        return array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $userRegistrations);
    }
}
