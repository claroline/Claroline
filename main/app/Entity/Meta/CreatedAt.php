<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAt
{
    /**
     * The creation date of the entity.
     *
     * @ORM\Column(name="createdAt", type="datetime")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Returns the entity's creation date.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the entity's creation date.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }
}
