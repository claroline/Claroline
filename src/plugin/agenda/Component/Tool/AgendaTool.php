<?php

namespace Claroline\AgendaBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaTool extends AbstractTool
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TranslatorInterface $translator
    ) {
    }

    public static function getName(): string
    {
        return 'agenda';
    }

    public static function getIcon(): string
    {
        return 'calendar';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (DesktopContext::getName() === $context) {
            // It would be better to directly handle it in the ui TOOL_LOAD action,
            // but for now I can't access the current user id. It will be possible when desktop context will contain the current user data
            return [
                'plannings' => $user instanceof User ? [
                    ['id' => $user->getUuid(), 'name' => $this->translator->trans('my_agenda', [], 'agenda'), 'locked' => true],
                ] : [],
            ];
        }

        return [
            'plannings' => [
                ['id' => $contextSubject->getUuid(), 'name' => $contextSubject->getName(), 'locked' => true],
            ],
        ];
    }
}
