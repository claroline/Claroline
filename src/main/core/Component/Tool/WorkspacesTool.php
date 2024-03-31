<?php

namespace Claroline\CoreBundle\Component\Tool;

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspacesTool extends AbstractTool
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    public static function getName(): string
    {
        return 'workspaces';
    }

    public static function getIcon(): string
    {
        return 'book';
    }

    public function supportsContext(string $context): bool
    {
        return DesktopContext::getName() === $context;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        return [
            'creatable' => $this->authorization->isGranted('CREATE', new Workspace()),
        ];
    }
}
