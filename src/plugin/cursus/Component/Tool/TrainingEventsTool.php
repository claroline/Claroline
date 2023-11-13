<?php

namespace Claroline\CursusBundle\Component\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CursusBundle\Entity\Session;

class TrainingEventsTool extends AbstractTool
{
    public function __construct(
        private readonly FinderProvider $finder
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

        return [
            'sessions' => $sessionList['data'],
        ];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
