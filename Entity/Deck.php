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
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="deck")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="Session", mappedBy="deck")
     */
    private $sessions;

    /**
     * @var integer
     *
     * @ORM\Column(name="new_card_day_default", type="integer")
     */
    private $new_card_day_default;

    /**
     * @var integer
     *
     * @ORM\Column(name="session_duration_default", type="integer")
     */
    private $session_duration_default;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
