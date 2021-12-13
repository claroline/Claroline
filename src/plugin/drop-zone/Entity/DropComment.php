<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Entity;

use Claroline\CoreBundle\Entity\AbstractComment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_dropzonebundle_drop_comment")
 */
class DropComment extends AbstractComment
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Drop",
     *     inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="drop_id", onDelete="CASCADE")
     *
     * @var Drop
     */
    private $drop;

    public function getDrop(): ?Drop
    {
        return $this->drop;
    }

    public function setDrop(Drop $drop)
    {
        $this->drop = $drop;
    }
}
