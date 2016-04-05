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
 * @ORM\Table(name="claro_fcbundle_note_type")
 */
class NoteType extends AbstractResource
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="FieldLabel", mappedBy="note_type")
     */
    private $field_labels;

    /**
     * @ORM\OneToMany(targetEntity="CardType", mappedBy="note_type")
     */
    private $card_types;

    /**
     * @ORM\OneToMany(targetEntity="Node", inversedBy="note_type")
     */
    private $nodes;

    public function __construct()
    {
        // Not imlemented yet.
    }
}
