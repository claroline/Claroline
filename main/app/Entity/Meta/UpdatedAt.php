<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAt
{
    /**
     * The last update date of the entity.
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Returns the entity's last update date.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the entity's last update date.
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
