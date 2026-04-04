<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project_annotation')]
#[ORM\Entity]
class Annotation
{
    use Id;
    use Uuid;

    /**
     * The drop this annotation is attached to.
     */
    #[ORM\ManyToOne(targetEntity: Drop::class, inversedBy: 'annotations')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Drop $drop = null;

    /**
     * The trainer who wrote this annotation.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'corrector_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $corrector = null;

    /**
     * Themed category of the annotation (e.g. "Structure", "References", "Style").
     * Categories are defined per milestone by the trainer in Milestone::annotationCategories.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $category = null;

    /**
     * Content of the annotation written by the trainer.
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    /**
     * Date and time the annotation was created.
     */
    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $creationDate;

    public function __construct()
    {
        $this->refreshUuid();

        $this->creationDate = new \DateTime();
    }

    public function getDrop(): ?Drop
    {
        return $this->drop;
    }

    public function setDrop(?Drop $drop): void
    {
        $this->drop = $drop;
    }

    public function getCorrector(): ?User
    {
        return $this->corrector;
    }

    public function setCorrector(?User $corrector): void
    {
        $this->corrector = $corrector;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
    }
}
