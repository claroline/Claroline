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

#[ORM\Table(name: 'claro_dropzonebundle_revision_comment')]
#[ORM\Entity]
class RevisionComment extends AbstractComment
{
    /**
     *
     * @var Revision
     */
    #[ORM\JoinColumn(name: 'revision_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Revision::class, inversedBy: 'comments')]
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
