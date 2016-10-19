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
use JMS\Serializer\Annotation\Groups;

/**
 * UserPreference.
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
     * @var int
     *
     * @ORM\Column(name="new_card_day", type="integer")
     * @Groups({"api_flashcard", "api_flashcard_user_pref"})
     */
    private $newCardDay;

    /**
     * @var int
     *
     * @ORM\Column(name="session_duration", type="integer")
     */
    private $sessionDuration;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string")
     * @Groups({"api_flashcard", "api_flashcard_user_pref"})
     */
    protected $theme;

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

    /**
     * @param int $newCardDay
     *
     * @return UserPreference
     */
    public function setNewCardDay($newCardDay)
    {
        $this->newCardDay = $newCardDay;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewCardDay()
    {
        return $this->newCardDay;
    }

    /**
     * @param int $sessionDuration
     *
     * @return UserPreference
     */
    public function setSessionDuration($sessionDuration)
    {
        $this->sessionDuration = $sessionDuration;

        return $this;
    }

    /**
     * @return int
     */
    public function getSessionDuration()
    {
        return $this->sessionDuration;
    }

    /**
     * @param string $theme
     *
     * @return Deck
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        if (!empty($this->theme)) {
            return $this->theme;
        } else {
            return Deck::THEME_DEFAULT['value'];
        }
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
