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
 * CardLearning
 *
 * @ORM\Table(name="claro_fcbundle_card_learning")
 * @ORM\Entity(repositoryClass="Claroline\FlashCardBundle\Repository\CardLearningRepository")
 */
class CardLearning
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="factor", type="float")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $factor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="painfull", type="boolean")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $painfull;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_repeated", type="integer")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $numberRepeated;

    /**
     * @var date
     *
     * @ORM\Column(name="due_date", type="date")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $dueDate;

    /**
     * @ORM\ManyToOne(targetEntity="Card")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $card;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     * })
     */
    private $user;

    public function __construct()
    {
        $this->factor = 1.3;
        $this->painfull = false;
        $this->numberRepeated = 0;
        $this->dueDate = null;
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
     * @param float $factor
     *
     * @return CardLearning
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
     * @param boolean $painfull
     *
     * @return CardLearning
     */
    public function setPainfull($painfull)
    {
        $this->painfull = $painfull;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getPainfull()
    {
        return $this->painfull;
    }

    /**
     * @param integer $numberRepeated
     *
     * @return CardLearning
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
     * @param Date $dueDate
     *
     * @return CardLearning
     */
    public function setDueDate(Date $dueDate)
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
     * @return CardLearning
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
     * @return CardLearning
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
