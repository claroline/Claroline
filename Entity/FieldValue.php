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
 * FieldValue
 *
 * @ORM\Table(name="claro_fcbundle_field_value")
 * @ORM\Entity
 */
class FieldValue
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
     * @var text
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FieldLabel", 
     *     inversedBy="field_values",
     *     onDelete="CASCADE",
     *     onUpdate="CASCADE"
     *  )
     */
    private $field_label;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Note", 
     *     inversedBy="field_values",
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
