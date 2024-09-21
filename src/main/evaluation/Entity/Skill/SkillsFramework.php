<?php

namespace Claroline\EvaluationBundle\Entity\Skill;

use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_evaluation_skills_framework')]
#[ORM\Entity]
class SkillsFramework implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use Name;
    use Description;

    #[ORM\OneToMany(targetEntity: \Claroline\EvaluationBundle\Entity\Skill\Skill::class, mappedBy: 'skillsFramework', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $skills;

    #[ORM\JoinTable(name: 'claro_evaluation_skills_frameworks_workspaces')]
    #[ORM\JoinColumn(name: 'skills_framework_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'workspace_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: \Claroline\CoreBundle\Entity\Workspace\Workspace::class)]
    private Collection $workspaces;

    public function __construct()
    {
        $this->refreshUuid();

        $this->skills = new ArrayCollection();
        $this->workspaces = new ArrayCollection();
    }

    public function getMimeType(): string
    {
        return 'skills_framework';
    }

    public static function getIdentifiers(): array
    {
        return ['id'];
    }

    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function getSkill(string $skillId): ?Skill
    {
        foreach ($this->skills as $skill) {
            if ($skill->getUuid() === $skillId) {
                return $skill;
            }
        }

        return null;
    }

    public function addSkill(Skill $skill): void
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setSkillsFramework($this);
        }
    }

    public function removeSkill(Skill $skill): void
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
            $skill->setSkillsFramework(null);
        }
    }

    public function getWorkspaces(): Collection
    {
        return $this->workspaces;
    }

    public function addWorkspace(Workspace $workspace): void
    {
        if (!$this->workspaces->contains($workspace)) {
            $this->workspaces->add($workspace);
        }
    }

    public function removeWorkspace(Workspace $workspace): void
    {
        if ($this->workspaces->contains($workspace)) {
            $this->workspaces->removeElement($workspace);
        }
    }
}
