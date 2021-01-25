<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAt
{
    /**
     * The last update date of the entity.
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    protected $updatedAt;

    /**
     * Returns the entity's last update date.
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Sets the entity's last update date.
     */
    public function setUpdatedAt(\DateTimeInterface $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }
}
