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
 * CardType
 *
 * @ORM\Table(name="claro_fcbundle_card_type")
 * @ORM\Entity
 */
class CardType
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="FieldLabel")
     * @ORM\JoinColumn(onDelete="CASCADE", onUpdate="CASCADE")
     */
    private $questions;

    /**
     * @ORM\ManyToMany(targetEntity="FieldLabel")
     * @ORM\JoinColumn(onDelete="CASCADE", onUpdate="CASCADE")
     */
    private $answers;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
