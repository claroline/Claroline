<?php

namespace Claroline\EvaluationBundle\Entity\Skill;

use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_evaluation_ability')]
#[ORM\Entity]
class Ability
{
    use Id;
    use Uuid;
    use Order;
    use Description;

    #[ORM\JoinColumn(name: 'skill_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\EvaluationBundle\Entity\Skill\Skill::class, inversedBy: 'abilities')]
    private ?Skill $skill = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    /**
     * @internal use Skill::addAbility(Ability $ability).
     */
    public function setSkill(?Skill $skill): void
    {
        $this->skill = $skill;
    }
}
