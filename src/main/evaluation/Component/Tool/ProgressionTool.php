<?php

namespace Claroline\EvaluationBundle\Component\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProgressionTool extends AbstractTool
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'progression';
    }

    public function supportsContext(string $context): bool
    {
        return WorkspaceContext::getName() === $context;
    }

    public static function getIcon(): string
    {
        return 'medal';
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            return [];
        }

        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $contextSubject,
            'user' => $this->tokenStorage->getToken()->getUser(),
        ]);

        if (empty($workspaceEvaluation)) {
            $workspaceEvaluation = new Evaluation();
        }

        return [
            'workspaceEvaluation' => $this->serializer->serialize($workspaceEvaluation),
            'resourceEvaluations' => $this->finder->search(ResourceUserEvaluation::class, [
                'filters' => ['workspace' => $contextSubject->getContextIdentifier(), 'user' => $user->getUuid()],
            ])['data'],
        ];
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }
}
