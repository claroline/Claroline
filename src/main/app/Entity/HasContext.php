<?php

namespace Claroline\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait HasContext
{
    /**
     * @ORM\Column(name="context_name")
     */
    private string $contextName;

    /**
     * @ORM\Column(name="context_id", nullable=true)
     */
    private ?string $contextId = null;

    public function getContextName(): string
    {
        return $this->contextName;
    }

    public function setContextName(string $contextName): void
    {
        $this->contextName = $contextName;
    }

    public function getContextId(): ?string
    {
        return $this->contextId;
    }

    public function setContextId(?string $contextId): void
    {
        $this->contextId = $contextId;
    }
}
