<?php

namespace Claroline\MessageBundle\Component\Tool;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\MessageBundle\Entity\Message;

class MessagesTool extends AbstractTool
{
    public function __construct(
        private readonly Crud $crud
    ) {
    }

    public static function getName(): string
    {
        return 'messaging';
    }

    public static function getIcon(): string
    {
        return 'envelope';
    }

    public function supportsContext(string $context): bool
    {
        return DesktopContext::getName() === $context;
    }

    public function getStatus(string $context, ContextSubjectInterface $contextSubject = null): ?int
    {
        return $this->crud->count(Message::class, [
            'read' => false,
            'removed' => false,
            'sent' => false,
        ]);
    }
}
