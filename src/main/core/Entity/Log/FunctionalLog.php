<?php

namespace Claroline\CoreBundle\Entity\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_functionnal")
 */
class FunctionalLog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $date;

    /**
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @ORM\Column(type="string")
     */
    private $event;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $user;

    /**
     * @var ResourceNode
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $resource;

    /**
     * @var Workspace
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $workspace;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $dateTime): self
    {
        $this->date = $dateTime;

        return $this;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getResource(): ?ResourceNode
    {
        return $this->resource;
    }

    public function setResource(?ResourceNode $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): self
    {
        $this->workspace = $workspace;

        return $this;
    }
}
