<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_text')]
#[ORM\Entity]
class Text extends AbstractResource
{
    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 1;

    /**
     * @var Collection<int, Revision>
     */
    #[ORM\OneToMany(targetEntity: Revision::class, mappedBy: 'text', cascade: ['persist'])]
    #[ORM\OrderBy(['version' => 'DESC'])]
    private Collection $revisions;

    public function __construct()
    {
        parent::__construct();

        $this->revisions = new ArrayCollection();
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(Revision $revision): void
    {
        $this->revisions->add($revision);
    }

    public function removeRevision(Revision $revision): void
    {
        $this->revisions->removeElement($revision);
    }

    /**
     * Get the current content of the Resource.
     */
    public function getContent(): ?string
    {
        if (0 < $this->revisions->count()) {
            foreach ($this->revisions as $revision) {
                if ($revision->getVersion() === $this->getVersion()) {
                    return $revision->getContent();
                }
            }
        }

        return null;
    }
}
