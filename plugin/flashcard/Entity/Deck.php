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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="claro_fcbundle_deck")
 * @ORM\Entity
 */
class Deck extends AbstractResource
{
    /**
     * List of the available themes.
     *
     * 'name' contains the name of the theme.
     * 'value' contains the name of the css file.
     */
    public static $themes = [
        [
            'name' => 'Standard',
            'value' => 'theme-std',
        ],
        [
            'name' => 'Green',
            'value' => 'theme-green',
        ],
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_flashcard", "api_flashcard_deck"})
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="deck")
     * @Groups({"api_flashcard", "api_flashcard_deck"})
     */
    protected $notes;

    /**
     * @ORM\OneToMany(targetEntity="Session", mappedBy="deck")
     */
    protected $sessions;

    /**
     * @var int
     *
     * @ORM\Column(name="new_card_day_default", type="integer")
     * @Groups({"api_flashcard", "api_flashcard_deck"})
     */
    protected $newCardDayDefault;

    /**
     * @var int
     *
     * @ORM\Column(name="session_duration_default", type="integer")
     * @Groups({"api_flashcard", "api_flashcard_deck"})
     */
    protected $sessionDurationDefault;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", )
     * @Groups({"api_flashcard", "api_flashcard_deck"})
     */
    protected $theme = 'theme-std';

    /**
     * @ORM\OneToMany(targetEntity="UserPreference", mappedBy="deck")
     */
    protected $userPreferences;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->newCardDayDefault = 5;
        $this->sessionDurationDefault = 10;
        $this->userPreferences = new ArrayCollection();
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
     * @param ArrayCollection $obj
     *
     * @return Deck
     */
    public function setNotes(ArrayCollection $obj)
    {
        $this->notes = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param ArrayCollection $obj
     *
     * @return Deck
     */
    public function setSessions(ArrayCollection $obj)
    {
        $this->sessions = $obj;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @param User     $user
     * @param DateTime $date
     *
     * @return Session
     */
    public function getSession(User $user, \DateTime $date)
    {
        foreach ($this->sessions as $session) {
            $interval = $date->diff($session->getDate(), true);
            if ($interval->days === 0 &&
               $session->getUser()->getId() === $user->getId()) {
                return $session;
            }
        }
    }

    /**
     * @param int $newCardDayDefault
     *
     * @return Deck
     */
    public function setNewCardDayDefault($newCardDayDefault)
    {
        $this->newCardDayDefault = $newCardDayDefault;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewCardDayDefault()
    {
        return $this->newCardDayDefault;
    }

    /**
     * @param int $sessionDurationDefault
     *
     * @return Deck
     */
    public function setSessionDurationDefault($sessionDurationDefault)
    {
        $this->sessionDurationDefault = $sessionDurationDefault;

        return $this;
    }

    /**
     * @return int
     */
    public function getSessionDurationDefault()
    {
        return $this->sessionDurationDefault;
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
            return self::$themes[0]['value'];
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getUserPreferences()
    {
        return $this->userPreferences;
    }

    /**
     * Return the preference for a user. If the given user doesn't have
     * personnal preferences, the default preferences are used.
     *
     * @param User $user
     *
     * @return UserPreference
     */
    public function getUserPreference(User $user)
    {
        foreach ($this->userPreferences as $userPref) {
            if ($user->getId() === $userPref->getUser()->getId()) {
                return $userPref;
            }
        }

        $userPref = new UserPreference();
        $userPref->setNewCardDay($this->newCardDayDefault);
        $userPref->setSessionDuration($this->sessionDurationDefault);
        $userPref->setTheme($this->getTheme());
        $userPref->setUser($user);
        $userPref->setDeck($this);

        return $userPref;
    }

    /**
     * Set the preferences for a specified user. If the user already has
     * preferences, then the new one overwrite the old one. If the user
     * has no prefences yet, the given preferences are added.
     *
     * @param UserPreference $newUserPref
     *
     * @return Deck
     */
    public function setUserPreference(UserPreference $newUserPref)
    {
        foreach ($this->userPreferences as $i => $userPref) {
            if ($newUserPref->getUser()->getId() === $userPref->getUser()->getId()) {
                $this->userPreferences[$i] = $newUserPref;

                return $this;
            }
        }
        $this->userPreferences->add($newUserPref);

        return $this;
    }

    /**
     * Return the list of available theme.
     *
     * @return array
     */
    public static function getAllThemes()
    {
        return self::$themes;
    }
}
