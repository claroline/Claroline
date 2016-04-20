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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Session
 *
 * @ORM\Table(name="claro_fcbundle_session")
 * @ORM\Entity
 */
class Session
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_session"
     * })
     */
    private $id;

    /**
     * @var date
     *
     * @ORM\Column(name="due_date", type="date")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_session"
     * })
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_session"
     * })
     */
    private $duration;

    /**
     * @ORM\ManyToMany(targetEntity="Card")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $cards;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Deck", inversedBy="sessions")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $deck;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->duration = 0;
        $this->cards = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DateTime $date
     *
     * @return Session
     */
    public function setDate(DateTime $date)
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
     * @param integer $duration
     *
     * @return Session
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param Card
     *
     * @return boolean
     */
    public function addCard(Card $obj)
    {
        if($this->cards->contains($obj)) {
            return false;
        } else {
            return $this->cards->add($obj);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return Session
     */
    public function setCards(ArrayCollection $obj)
    {
        $this->cards = $obj;

        return $this;
    }

    /**
     * @param Deck $obj
     *
     * @return Session
     */
    public function setDeck(Deck $obj)
    {
        $this->deck = $obj;

        return $this;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }


    /**
     * @param User $obj
     *
     * @return Session
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
