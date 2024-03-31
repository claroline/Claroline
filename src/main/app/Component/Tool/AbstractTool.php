<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;

abstract class AbstractTool implements ToolInterface
{
    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return false;
    }

    public function supportsSubject(ContextSubjectInterface $subject): bool
    {
        return true;
    }

    public function getStatus(string $context, ContextSubjectInterface $contextSubject = null): ?int
    {
        return null;
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        return null;
    }

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
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
