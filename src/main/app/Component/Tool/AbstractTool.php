<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\CoreBundle\Entity\User;

abstract class AbstractTool implements ToolInterface
{
    public static function getAdditionalRights(): array
    {
        return [];
    }

    public function isRequired(string $context, ?string $contextId): bool
    {
    }

    public function open(string $context, mixed $contextObject = null, User $user = null): array
    {
        return [];
    }

    public function configure()
    {
    }

    public function import(): void
    {
    }

    public function export()
    {
    }
}
