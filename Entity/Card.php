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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Card
 *
 * @ORM\Table(name="claro_fcbundle_card")
 * @ORM\Entity
 */
class Card
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CardType", inversedBy="cards")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $cardType;

    /**
     * @ORM\ManyToOne(targetEntity="Note", inversedBy="cards")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $note;

    public function __construct()
    {
        // Not imlemented yet.
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
     * @param CardType $obj
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
