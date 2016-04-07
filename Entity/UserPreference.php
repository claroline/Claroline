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

/**
 * UserPreference
 *
 * @ORM\Table(name="claro_fcbundle_user_preference",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="uniq", columns={"user", "deck"})
 *     }
 *  )
 * @ORM\Entity
 */
class UserPreference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="new_card_day", type="integer")
     */
    private $newCardDay;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_duration", type="integer")
     */
    private $sessionDuration;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="deck", inversedBy="userPreferences")
     * @ORM\JoinColumn(name="deck", onDelete="CASCADE")
     */
    private $deck;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user", onDelete="CASCADE")
     */
    private $user;

    public function __construct()
    {
        // Not imlemented yet.
    }

    /**
     * @param integer $newCardDay
     *
     * @return UserPreference
     */
    public function setNewCardDay($newCardDay)
    {
        $this->newCardDay= $newCardDay;

        return $this;
    }

    /**
     * @return integer
     */
    public function getNewCardDay()
    {
        return $this->newCardDay;
    }

    /**
     * @param integer $sessionDuration
     *
     * @return UserPreference
     */
    public function setSessionDuration($sessionDuration)
    {
        $this->sessionDuration= $sessionDuration;

        return $this;
    }

    /**
     * @return integer
     */
    public function getSessionDuration()
    {
        return $this->sessionDuration;
    }

    /**
     * @param Deck $obj
     *
     * @return UserPreference
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
     * @return UserPreference
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
