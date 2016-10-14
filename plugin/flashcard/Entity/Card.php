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

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Card.
 *
 * @ORM\Table(name="claro_fcbundle_card")
 * @ORM\Entity(repositoryClass="Claroline\FlashCardBundle\Repository\CardRepository")
 */
class Card
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CardType", inversedBy="cards")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card",
     *     "api_flashcard_note",
     *     "api_flashcard_deck"
     * })
     */
    private $cardType;

    /**
     * @ORM\ManyToOne(targetEntity="Note", inversedBy="cards")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({
     *     "api_flashcard",
     *     "api_flashcard_card"
     * })
     */
    private $note;

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
     * @param CardType $obj
     *
     * @return Card
     */
    public function setCardType(CardType $obj)
    {
        $this->cardType = $obj;

        return $this;
    }

    /**
     * @return CardType
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @param Note $obj
     *
     * @return Card
     */
    public function setNote(Note $obj)
    {
        $this->note = $obj;

        return $this;
    }

    /**
     * @return Note
     */
    public function getNote()
    {
        return $this->note;
    }
}
