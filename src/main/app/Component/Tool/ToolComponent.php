<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;

abstract class ToolComponent implements ToolInterface
{
    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool
    {
        return false;
    }

    public function supportsSubject(ContextSubjectInterface $subject): bool
    {
        return true;
    }

    public function getStatus(string $context, ?ContextSubjectInterface $contextSubject = null): mixed
    {
        return null;
    }

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array
    {
        return null;
    }

    public function create(string $context, ?ContextSubjectInterface $contextSubject = null, array $configData = []): void
    {
    }

    public function configure(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null, array $configData = []): ?array
    {
        return [];
    }

    public function search(string $context, ?ContextSubjectInterface $contextSubject = null, string $search = ''): ?array
    {
        return [];
    }

    public function export(string $context, ?ContextSubjectInterface $contextSubject = null, ?FileBag $fileBag = null): ?array
    {
        return [];
    }

    public function import(string $context, ?ContextSubjectInterface $contextSubject = null, ?FileBag $fileBag = null, array $data = [], array $entities = []): ?array
    {
        return [];
    }
}
