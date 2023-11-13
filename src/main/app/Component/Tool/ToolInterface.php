<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;

interface ToolInterface extends ComponentInterface, ContextualInterface
{
    /**
     * Gets the lists of custom rights for the tool.
     *
     * @return array - an array of strings containing the rights names
     */
    public static function getAdditionalRights(): array;

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array;

    public function configure(string $context, ContextSubjectInterface $contextSubject = null, array $configData = []): ?array;

    public function export(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null): ?array;

    public function import(string $context, ContextSubjectInterface $contextSubject = null, FileBag $fileBag = null, array $data = [], array $entities = []): ?array;
}
