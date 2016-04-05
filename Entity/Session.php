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
 * Session
 *
 * @ORM\Table(name="claro_fcbundle_session")
 * @ORM\Entity
 */
class Session
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
     * @var date
     *
     * @ORM\Column(name="due_date", type="date")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @ORM\ManyToMany(targetEntity="Card")
     * @ORM\JoinColumn(onDelete="CASCADE", onUpdate="CASCADE")
     */
    private $cards;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Deck", 
     *     inversedBy="sessions",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $deck;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
