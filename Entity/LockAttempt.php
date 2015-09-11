<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\LockAttempt.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_lock_attempt")
 */
class LockAttempt
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="key_lock", type="string", length=255)
     */
    private $keyLock;

    /**
     * @var date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Paper")
     */
    private $paper;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set keyLock.
     *
     * @param string $keyLock
     */
    public function setKeyLock($keyLock)
    {
        $this->keyLock = $keyLock;
    }

    /**
     * Get keyLock.
     *
     * @return string
     */
    public function getKeyLock()
    {
        return $this->keyLock;
    }

    /**
     * Set date.
     *
     * @param date $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date.
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }

    public function setPaper(\UJM\ExoBundle\Entity\Paper $paper)
    {
        $this->paper = $paper;
    }

    public function getPaper()
    {
        return $this->paper;
    }
}
