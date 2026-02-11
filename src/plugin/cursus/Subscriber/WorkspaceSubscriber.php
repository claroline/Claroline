<?php

namespace Claroline\CursusBundle\Subscriber;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Workspace\AccessRestrictedWorkspaceEvent;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Manager\CourseManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
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

        // check if the workspace is linked to any course to display it to the user
        // in the place of standard restrictions
        $courses = $this->om->getRepository(Course::class)->findByWorkspace($workspace);
        if (!empty($courses)) {
            $course = $courses[0];
            $event->addError('NOT_REGISTERED_TRAINING', 'You are not registered to the workspace course.', $this->courseManager->open($course));
        }
    }
}
