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
            if ($interval->days == 0 and
               $session->getUser()->getId() == $user->getId()) {
                return $session;
            }
        }

        return;
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
            if ($user->getId() == $userPref->getUser()->getId()) {
                return $userPref;
            }
        }

        $userPref = new UserPreference();
        $userPref->setNewCardDay($this->newCardDayDefault);
        $userPref->setSessionDuration($this->sessionDurationDefault);
        $userPref->setUser($user);
        $userPref->setDeck($this);

        return $userPref;
    }
}
