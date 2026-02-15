<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;

interface ToolInterface extends ComponentInterface, ContextualInterface
{
    public static function getIcon(): string;

    /**
     * Checks if the tool is required in the specified context.
     */
    public function isRequired(string $context, ?ContextSubjectInterface $contextSubject = null): bool;

    public function getStatus(string $context, ?ContextSubjectInterface $contextSubject = null): mixed;

    public function open(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null): ?array;

    public function create(string $context, ?ContextSubjectInterface $contextSubject = null, array $configData = []): void;

    public function configure(OrderedTool $tool, string $context, ?ContextSubjectInterface $contextSubject = null, array $configData = []): ?array;

    public function search(string $context, ?ContextSubjectInterface $contextSubject = null, string $search = ''): ?array;

    public function export(string $context, ?ContextSubjectInterface $contextSubject = null, ?FileBag $fileBag = null): ?array;

    public function import(string $context, ?ContextSubjectInterface $contextSubject = null, ?FileBag $fileBag = null, array $data = [], array $entities = []): ?array;
}
