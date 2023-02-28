<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAt
{
    /**
     * The creation date of the entity.
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    protected $createdAt;

    /**
     * Returns the entity's creation date.
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Sets the entity's creation date.
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }
}
