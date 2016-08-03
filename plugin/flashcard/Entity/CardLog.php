<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\FlashCardBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * CardLog.
 *
 * @ORM\Table(name="claro_fcbundle_card_log")
 * @ORM\Entity(repositoryClass="Claroline\FlashCardBundle\Repository\CardLogRepository")
 */
class CardLog
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
     * @var date
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var float
     *
     * @ORM\Column(name="factor", type="float")
     */
    private $factor;

    /**
     * @var bool
     *
     * @ORM\Column(name="painful", type="boolean")
     */
    private $painful;

    /**
     * @var int
     *
     * @ORM\Column(name="number_repeated", type="integer")
     */
    private $numberRepeated;

    /**
     * @var date
     *
     * @ORM\Column(name="due_date", type="date")
     */
    private $dueDate;

    /**
     * @ORM\ManyToOne(targetEntity="Card")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $card;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

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
     * @param DateTime $date
     *
     * @return CardLog
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param float $factor
     *
     * @return CardLog
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;

        return $this;
    }

    /**
     * @return float
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * @param bool $painful
     *
     * @return CardLog
     */
    public function setPainful($painful)
    {
        $this->painful = $painful;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPainful()
    {
        return $this->painful;
    }

    /**
     * @param int $numberRepeated
     *
     * @return CardLog
     */
    public function setNumberRepeated($numberRepeated)
    {
        $this->numberRepeated = $numberRepeated;

        return $this;
    }

    /**
     * @return float
     */
    public function getNumberRepeated()
    {
        return $this->numberRepeated;
    }

    /**
     * @param DateTime $dueDate
     *
     * @return CardLog
     */
    public function setDueDate(\DateTime $dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * @return Date
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param Card $obj
     *
     * @return CardLog
     */
    public function setCard(Card $obj)
    {
        $this->card = $obj;

        return $this;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param User $obj
     *
     * @return CardLog
     */
    public function setUser(User $obj)
    {
        $this->user = $obj;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
