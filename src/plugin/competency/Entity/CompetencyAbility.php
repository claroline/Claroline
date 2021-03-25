<?php

namespace HeVinci\CompetencyBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\CompetencyAbilityRepository")
 * @ORM\Table(name="hevinci_competency_ability")
 */
class CompetencyAbility
{
    use Uuid;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Competency", inversedBy="competencyAbilities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competency;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Ability",
     *     inversedBy="competencyAbilities",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $ability;

    /**
     * @ORM\ManyToOne(targetEntity="Level", inversedBy="competencyAbilities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $level;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setCompetency(Competency $competency)
    {
        $this->competency = $competency;
        $competency->addCompetencyAbility($this);
    }

    /**
     * @return Competency
     */
    public function getCompetency()
    {
        return $this->competency;
    }

    public function setAbility(Ability $ability)
    {
        $this->ability = $ability;
    }

    /**
     * @return Ability
     */
    public function getAbility()
    {
        return $this->ability;
    }

    public function setLevel(Level $level)
    {
        $this->level = $level;
    }

    /**
     * @return Level
     */
    public function getLevel()
    {
        return $this->level;
    }
}
