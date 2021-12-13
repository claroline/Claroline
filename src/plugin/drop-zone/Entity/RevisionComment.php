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
 * @ORM\Table(name="claro_dropzonebundle_revision_comment")
 */
class RevisionComment extends AbstractComment
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Revision",
     *     inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="revision_id", onDelete="CASCADE")
     *
     * @var Revision
     */
    private $revision;

    public function getRevision(): ?Revision
    {
        return $this->revision;
    }

    public function setRevision(Revision $revision)
    {
        $this->revision = $revision;
    }
}
