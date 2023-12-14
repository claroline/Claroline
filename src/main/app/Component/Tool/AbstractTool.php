<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;

abstract class AbstractTool implements ToolInterface
{
    public static function getAdditionalRights(): array
    {
        return [];
    }

    public function isRequired(string $context, ?string $contextId): bool
    {
        return false;
    }

    public function supportsSubject(ContextSubjectInterface $subject): bool
    {
        return true;
    }

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array
    {
        return [];
    }

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        return [];
    }
}
