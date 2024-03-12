<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;

interface ToolInterface extends ComponentInterface, ContextualInterface
{
    public static function getIcon(): string;

    /**
     * Checks if the tool is required in the specified context.
     */
    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool;

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array;

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array;

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array;

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array;
}
