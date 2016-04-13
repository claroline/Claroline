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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="claro_fcbundle_deck")
 * @ORM\Entity
 */
class Deck extends AbstractResource
{
    /**
     * @var integer
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
     * @var integer
     *
     * @ORM\Column(name="new_card_day_default", type="integer")
     * @Groups({"api_flashcard", "api_flashcard_deck"})
     */
    protected $newCardDayDefault;

    /**
     * @var integer
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
        $this->newCardDayDefault = 10;
        $this->sessionDurationDefault = 10;
        $this->userPreferences = new ArrayCollection();
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
     * @param integer $newCardDayDefault
     *
     * @return Deck
     */
    public function setNewCardDayDefault($newCardDayDefault)
    {
        $this->newCardDayDefault = $newCardDayDefault;

        return $this;
    }

    /**
     * @return integer
     */
    public function getNewCardDayDefault()
    {
        return $this->newCardDayDefault;
    }

    /**
     * @param integer $sessionDurationDefault
     *
     * @return Deck
     */
    public function setSessionDurationDefault($sessionDurationDefault)
    {
        $this->sessionDurationDefault= $sessionDurationDefault;

        return $this;
    }

    /**
     * @return integer
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

}
