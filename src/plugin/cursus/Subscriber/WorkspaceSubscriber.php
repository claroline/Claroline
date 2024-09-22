<?php

namespace Claroline\CursusBundle\Subscriber;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Workspace\AccessRestrictedWorkspaceEvent;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\CourseManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly CourseManager $courseManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkspaceEvents::ACCESS_RESTRICTED => 'onRestrictedAccess',
        ];
    }

    public function onRestrictedAccess(AccessRestrictedWorkspaceEvent $event): void
    {
        $workspace = $event->getWorkspace();

        // check if the workspace is linked to any course in order to display it to the user
        // in the place of standard restrictions
        $courses = $this->om->getRepository(Course::class)->findByWorkspace($workspace);
        if (!empty($courses)) {
            $course = $courses[0];
            $defaultSession = null;

            $user = $this->tokenStorage->getToken()?->getUser();
            if ($user instanceof User) {
                if ($this->om->getRepository(Course::class)->isFullyRegistered($course, $user)) {
                    return;
                }
            }

            // search for sessions in which the current user is registered
            $user = $this->tokenStorage->getToken()?->getUser();
            $registrations = [];
            if ($user instanceof User) {
                $registrations = $this->courseManager->getRegistrations($course, $user);

                // by default display one of the session the user is registered to
                if (!empty($registrations['users'])) {
                    $defaultSession = $this->om->getRepository(Session::class)->findOneBy([
                        'uuid' => $registrations['users'][0]['session']['id'],
                    ]);
                } elseif (!empty($registrations['groups'])) {
                    $defaultSession = $this->om->getRepository(Session::class)->findOneBy([
                        'uuid' => $registrations['groups'][0]['session']['id'],
                    ]);
                }
            }

            $sessions = $this->om->getRepository(Session::class)->findAvailable($course);

            if (empty($defaultSession)) {
                // current user is not registered to any session yet
                // get the default session to open
                switch ($course->getSessionOpening()) {
                    case 'default':
                        $defaultSession = $course->getDefaultSession();
                        break;
                    case 'first_available':
                        if (!empty($sessions)) {
                            $defaultSession = $sessions[0];
                        }
                        break;
                }
            }

            $event->addError('trainings', [
                'course' => $this->serializer->serialize($course),
                'defaultSession' => $defaultSession ? $this->serializer->serialize($defaultSession) : null,
                'availableSessions' => array_map(function (Session $session) {
                    return $this->serializer->serialize($session);
                }, $sessions),
                'registrations' => $registrations,
            ]);
        }
    }
}
