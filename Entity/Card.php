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
     * @ORM\ManyToOne(
     *     targetEntity="CardType", 
     *     inversedBy="cards",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $card_type;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Note", 
     *     inversedBy="cards",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $note;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
