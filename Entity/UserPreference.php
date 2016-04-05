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
 * UserPreference
 *
 * @ORM\Table(name="claro_fcbundle_user_preference",
 *     uniqueConstraints={
 *         @UniqueConstaint(name="uniq", columns={"user", "deck"})
 *     }
 *  )
 * @ORM\Entity
 */
class UserPreference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="new_card_day", type="integer")
     */
    private $new_card_day;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_duration", type="integer")
     */
    private $session_duration;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="deck", 
     *     inversedBy="user_prefences",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $deck;

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
