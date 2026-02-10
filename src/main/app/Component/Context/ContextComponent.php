<?php

namespace Claroline\AppBundle\Component\Context;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class ContextComponent implements ContextInterface
{
    /**
     * @deprecated use ContextInterface::getSubject() instead
     */
    public function getObject(?string $contextId): ?ContextSubjectInterface
    {
        return $this->getSubject($contextId);
    }

    public function getAccessError(?TokenInterface $token, ?ContextSubjectInterface $contextSubject): ?array
    {
        return null;
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
