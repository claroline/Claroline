<?php

namespace ICAP\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Statusable
{
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED   = 1;

    /**
     * @var array
     */
    protected $statusList = array(
        self::STATUS_PUBLISHED,
        self::STATUS_UNPUBLISHED
    );

    /**
     * @var int $status
     *
     * @ORM\Column(type="smallint")
     */
    protected $status = self::STATUS_UNPUBLISHED;

    /**
     * @param integer $status
     *
     * @return Statusable
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->statusList)) {
            throw new \InvalidArgumentException(sprintf("Invalid status for %s.", __CLASS__));
        }
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Post
     */
    public function publish()
    {
        return $this->setStatus(self::STATUS_PUBLISHED);
    }

    /**
     * @return Post
     */
    public function unpublish()
    {
        return $this->setStatus(self::STATUS_UNPUBLISHED);
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->getStatus() === self::STATUS_PUBLISHED;
    }
}
