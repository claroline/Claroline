<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;

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
     */
    #[ORM\Column(type: Types::SMALLINT)]
    protected ?int $status = self::STATUS_UNPUBLISHED;

    /**
     * @param int $status
     *
     * @return Statusable
     *
     * @throws InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->statusList)) {
            throw new InvalidArgumentException(sprintf('Invalid status for %s.', __CLASS__));
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
        return self::STATUS_PUBLISHED === $this->getStatus();
    }
}
