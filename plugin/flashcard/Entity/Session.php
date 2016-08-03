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

/**
 * Session.
 *
 * @ORM\Table(name="claro_fcbundle_session")
 * @ORM\Entity
 */
class Session
{
    /**
     * @var int
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
     * @var int
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
     * @ORM\JoinTable(name="session_card_new")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $newCards;

    /**
     * @ORM\ManyToMany(targetEntity="Card")
     * @ORM\JoinTable(name="session_card_old")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $oldCards;

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
        $this->newCards = new ArrayCollection();
        $this->oldCards = new ArrayCollection();
    }

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
     * @return Session
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
     * @param int $duration
     *
     * @return Session
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param Card
     *
     * @return bool
     */
    public function deleteCard(Card $obj)
    {
        if ($this->newCards->contains($obj)) {
            return $this->newCards->removeElement($obj);
        }
        if ($this->oldCards->contains($obj)) {
            return $this->oldCards->removeElement($obj);
        }
    }

    /**
     * @param Card
     *
     * @return bool
     */
    public function addNewCard(Card $obj)
    {
        if ($this->newCards->contains($obj)) {
            return false;
        }

        return $this->newCards->add($obj);
    }

    /**
     * @return ArrayCollection
     */
    public function getNewCards()
    {
        return $this->newCards;
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return Session
     */
    public function setNewCards(ArrayCollection $obj)
    {
        $this->newCards = $obj;

        return $this;
    }

    /**
     * @param Card
     *
     * @return bool
     */
    public function addOldCard(Card $obj)
    {
        if ($this->oldCards->contains($obj)) {
            return false;
        }

        return $this->oldCards->add($obj);
    }

    /**
     * @return ArrayCollection
     */
    public function getOldCards()
    {
        return $this->oldCards;
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return Session
     */
    public function setOldCards(ArrayCollection $obj)
    {
        $this->oldCards = $obj;

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
