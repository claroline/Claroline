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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_template",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="template_unique_name", columns={"claro_template_type", "entity_name"})
 *     }
 * )
 */
class Template
{
    use Id;
    use Name;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\TemplateType")
     * @ORM\JoinColumn(name="claro_template_type", nullable=false, onDelete="CASCADE")
     *
     * @var TemplateType
     */
    private $type;

    /**
     * System templates can not be edited nor deleted by users.
     * They are managed through DataFixtures.
     *
     * @ORM\Column(name="is_system", type="boolean")
     *
     * @var bool
     */
    private $system = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Template\TemplateContent",
     *     mappedBy="template",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection|TemplateContent[]
     */
    private $contents;

    public function __construct()
    {
        $this->refreshUuid();

        $this->contents = new ArrayCollection();
    }

    public function getType(): ?TemplateType
    {
        return $this->type;
    }

    public function setType(TemplateType $type)
    {
        $this->type = $type;
    }

    public function isSystem(): bool
    {
        return $this->system;
    }

    public function setSystem(bool $system)
    {
        $this->system = $system;
    }

    public function getTemplateContents()
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

    public function addTemplateContent(TemplateContent $content)
    {
        if (!$this->contents->contains($content)) {
            $this->contents->add($content);
            $content->setTemplate($this);
        }
    }

    public function removeTemplateContent(TemplateContent $content)
    {
        if ($this->contents->contains($content)) {
            $this->contents->removeElement($content);
            $content->setTemplate(null);
        }
    }
}
