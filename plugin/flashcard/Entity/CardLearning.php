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
 * CardLearning.
 *
 * @ORM\Table(name="claro_fcbundle_card_learning")
 * @ORM\Entity(repositoryClass="Claroline\FlashCardBundle\Repository\CardLearningRepository")
 */
class CardLearning
{
    const ANS_AGAIN = 0, // Not good, repeat again
          ANS_HARD = 1,  // Good, but difficult to remember
          ANS_GOOD = 2,  // Good
          ANS_EASY = 3;  // Good and easy to remember

    /**
     * @var int
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
     * @var bool
     *
     * @ORM\Column(name="painful", type="boolean")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $painful;

    /**
     * @var int
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
        $this->painful = false;
        $this->numberRepeated = 0;
        $this->dueDate = new \DateTime();
    }

    /**
     * @param int $answerQuality
     *                           0 - Not good, repeat again
     *                           1 - Good, but difficult to remember
     *                           2 - Good
     *                           3 - Good and easy to remember
     *                           See constant of this class
     */
    public function study($answerQuality)
    {
        if ($answerQuality > 0) {
            ++$this->numberRepeated;
        } else {
            $this->numberRepeated = 0;
        }

        $this->updateFactor($answerQuality);
        $this->updateDueDate();
    }

    /**
     * @param int $answerQuality
     */
    public function updateFactor($answerQuality)
    {
        $newFactor = $this->factor;

        if ($answerQuality > self::ANS_AGAIN) {
            // The quality must be between 0 and 5
            $answerQuality += 2;
            $newFactor = $this->factor - 0.8 + 0.28 * $answerQuality - 0.02 * $answerQuality * $answerQuality;
        }

        if ($newFactor < 1.3) {
            $this->factor = 1.3;
        } elseif ($newFactor > 2.5) {
            $this->factor = 2.5;
        } else {
            $this->factor = $newFactor;
        }
    }

    public function updateDueDate()
    {
        if ($this->numberRepeated > 0) {
            $date = new \DateTime();

            if ($this->numberRepeated === 1) {
                $nbrDays = 1;
            } elseif ($this->numberRepeated === 2) {
                $nbrDays = 6;
            } else {
                $nbrDays = 6;
                for ($i = 2; $i <= $this->numberRepeated; ++$i) {
                    $nbrDays *= $this->factor;
                }
                $nbrDays = ceil($nbrDays);
            }

            $date->add(new \DateInterval('P'.$nbrDays.'D'));
            $date->setTime(0, 0);

            $this->dueDate = $date;
        }
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
     * @param bool $painful
     *
     * @return CardLearning
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
     * @param \DateTime $dueDate
     *
     * @return CardLearning
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
