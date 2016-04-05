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
 * Note
 *
 * @ORM\Table(name="claro_fcbundle_note")
 * @ORM\Entity
 */
class Note
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
     *     targetEntity="NodeType", 
     *     inversedBy="notes",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $node_type;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Deck", 
     *     inversedBy="notes",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $deck;

    /**
     * @ORM\OneToMany(targetEntity="FieldValue", mappedBy="note")
     */
    private $field_values;

    /**
     * @ORM\OneToMany(targetEntity="Card", mappedBy="note")
     */
    private $cards;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
