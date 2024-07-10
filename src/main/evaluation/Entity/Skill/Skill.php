<?php

namespace Claroline\EvaluationBundle\Entity\Skill;

use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_evaluation_skill")
 */
class Skill
{
    use Id;
    use Uuid;
    use Order;
    use Description;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\EvaluationBundle\Entity\Skill\SkillsFramework", inversedBy="skills")
     * @ORM\JoinColumn(name="skills_framework_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?SkillsFramework $skillsFramework = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\EvaluationBundle\Entity\Skill\Skill", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Skill $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\EvaluationBundle\Entity\Skill\Skill", mappedBy="parent", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\EvaluationBundle\Entity\Skill\Ability", mappedBy="skill", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private Collection $abilities;

    public function __construct()
    {
        $this->refreshUuid();

        $this->children = new ArrayCollection();
        $this->abilities = new ArrayCollection();
    }

    public function getSkillsFramework(): ?SkillsFramework
    {
        return $this->skillsFramework;
    }

    /**
     * @internal use SkillsFramework::addSkill(Skill $skill)
     */
    public function setSkillsFramework(?SkillsFramework $skillsFramework): void
    {
        $this->skillsFramework = $skillsFramework;
    }

    public function getParent(): ?Skill
    {
        return $this->parent;
    }

    /**
     * @internal
     */
    public function setParent(?Skill $parent = null): void
    {
        $this->parent = $parent;
    }

    /** @return Skill[] */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getChild(string $skillId): ?Skill
    {
        foreach ($this->children as $child) {
            if ($child->getUuid() === $skillId) {
                return $child;
            }
        }

        return null;
    }

    public function addChild(Skill $skill): void
    {
        if (!$this->children->contains($skill)) {
            $this->children->add($skill);
            $skill->setParent($this);
            $skill->setSkillsFramework($this->skillsFramework);
        }
    }

    public function removeChild(Skill $skill): void
    {
        if ($this->children->contains($skill)) {
            $this->children->removeElement($skill);
            $skill->setParent(null);
        }
    }

    /** @return Ability[] */
    public function getAbilities(): Collection
    {
        return $this->abilities;
    }

    public function getAbility(string $abilityId): ?Ability
    {
        foreach ($this->abilities as $ability) {
            if ($ability->getUuid() === $abilityId) {
                return $ability;
            }
        }

        return null;
    }

    public function addAbility(Ability $ability): void
    {
        if (!$this->abilities->contains($ability)) {
            $this->abilities->add($ability);
            $ability->setSkill($this);
        }
    }

    public function removeAbility(Ability $ability): void
    {
        if ($this->abilities->contains($ability)) {
            $this->abilities->removeElement($ability);
            $ability->setSkill(null);
        }
    }
}
