<?php

namespace Claroline\AppBundle\Component\Context;

abstract class ContextComponent implements ContextInterface
{
    /**
     * @deprecated use ContextInterface::getSubject() instead
     */
    public function getObject(?string $contextId): ?ContextSubjectInterface
    {
        return $this->getSubject($contextId);
    }

    public function getAdditionalData(?ContextSubjectInterface $contextSubject): array
    {
        return [];
    }

    public function create(?ContextSubjectInterface $contextSubject, array $data): void
    {
    }

    public function update(?ContextSubjectInterface $contextSubject, array $data): void
    {
    }
}
