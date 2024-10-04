<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Template;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Finder\Template\TemplateType as TemplateFinder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_template')]
#[ORM\Entity]
#[CrudEntity(finderClass: TemplateFinder::class)]
class Template
{
    use Id;
    use Name;
    use Uuid;

    #[ORM\JoinColumn(name: 'claro_template_type', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: TemplateType::class)]
    private ?TemplateType $type = null;

    /**
     * System templates can not be edited nor deleted by users.
     * They are managed through DataFixtures.
     */
    #[ORM\Column(name: 'is_system', type: Types::BOOLEAN)]
    private bool $system = false;

    /**
     * @var Collection<int, TemplateContent>
     */
    #[ORM\OneToMany(targetEntity: TemplateContent::class, mappedBy: 'template', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $contents;

    public function __construct()
    {
        $this->refreshUuid();

        $this->contents = new ArrayCollection();
    }

    public function getType(): ?TemplateType
    {
        return $this->type;
    }

    public function setType(TemplateType $type): void
    {
        $this->type = $type;
    }

    public function isSystem(): bool
    {
        return $this->system;
    }

    public function setSystem(bool $system): void
    {
        $this->system = $system;
    }

    public function getTemplateContents(): Collection
    {
        return $this->contents;
    }

    public function getTemplateContent(string $lang): ?TemplateContent
    {
        foreach ($this->contents as $content) {
            if ($content->getLang() === $lang) {
                return $content;
            }
        }

        return null;
    }

    public function addTemplateContent(TemplateContent $content): void
    {
        if (!$this->contents->contains($content)) {
            $this->contents->add($content);
            $content->setTemplate($this);
        }
    }

    public function removeTemplateContent(TemplateContent $content): void
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            $content->setTemplate(null);
        }
    }
}
