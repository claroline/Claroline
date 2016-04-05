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
 * CardLearning
 *
 * @ORM\Table(name="claro_fcbundle_card_learning")
 * @ORM\Entity
 */
class CardLearning
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
     * @var float
     *
     * @ORM\Column(name="factor", type="float")
     */
    private $factor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="painfull", type="boolean")
     */
    private $painfull;

    /**
     * @var integer
     *
     * @ORM\Column(name="number_repeated", type="integer")
     */
    private $number_repeated;

    /**
     * @var date
     *
     * @ORM\Column(name="due_date", type="date")
     */
    private $due_date;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Card", 
     *     inversedBy="card_learnings",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $card;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $user;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
