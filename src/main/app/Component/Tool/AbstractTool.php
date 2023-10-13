<?php

namespace Claroline\AppBundle\Component\Tool;

abstract class AbstractTool implements ToolInterface
{
    public static function getAdditionalRights(): array
    {
        return [];
    }

    public function isRequired(string $context, ?string $contextId): bool
    {

    }

    public function open()
    {

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
