<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\ToolComponent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Manager\CourseManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TrainingsTool extends ToolComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly CourseManager $courseManager
    ) {
    }

    public static function getName(): string
    {
        return 'trainings';
    }

    public static function getIcon(): string
    {
        return 'graduation-cap';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($context === WorkspaceContext::getName()) {
            // retrieve the course bound to workspace if any
            $courses = $this->om->getRepository(Course::class)->findByWorkspace($contextSubject);

            $course = null;
            $registrations = [];
            if ($user && !empty($courses)) {
                $course = $courses[0];
                $registrations = $this->courseManager->getRegistrations($user, $course);
            }

            return [
                'course' => $course ? $this->serializer->serialize($course) : null,
                'registrations' => $registrations,
            ];
        }

        return [
            'registrations' => $user ? $this->courseManager->getRegistrations($user) : [],
        ];
    }
}
