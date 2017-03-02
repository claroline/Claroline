<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ExclusionPolicy("all")
 */
class Statusable
{
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED = 1;

    /**
     * @var array
     */
    protected $statusList = [
        self::STATUS_PUBLISHED,
        self::STATUS_UNPUBLISHED,
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $status = self::STATUS_UNPUBLISHED;

    /**
     * @param int $status
     *
     * @return Statusable
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->statusList)) {
            throw new \InvalidArgumentException(sprintf('Invalid status for %s.', __CLASS__));
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
     * @VirtualProperty
     * @Groups({"blog_list", "blog_post"})
     */
    public function isPublished()
    {
        return $this->getStatus() === self::STATUS_PUBLISHED;
    }
}
