<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Session;

class TrainingEventsTool extends AbstractTool
{
    public function __construct(
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly ObjectManager $om
    ) {
    }

    public static function getName(): string
    {
        return 'training_events';
    }

    public function supportsContext(string $context): bool
    {
        return WorkspaceContext::getName() === $context;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $sessionList = $this->finder->search(Session::class, [
            'filters' => ['workspace' => $contextSubject->getContextIdentifier()],
        ], [SerializerInterface::SERIALIZE_MINIMAL]);

        $courses = $this->om->getRepository(Course::class)->findByWorkspace($contextSubject);

        if (count($courses) <= 0) {
            return null;
        }

        $course = $this->serializer->serialize($courses[0]);
        $course['sessions'] = $sessionList['data'];

        return [
            'course' => $course,
        ];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
