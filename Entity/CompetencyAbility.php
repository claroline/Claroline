<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\CompetencyAbilityRepository")
 * @ORM\Table(name="hevinci_competency_ability")
 */
class CompetencyAbility
{
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
     * @ORM\ManyToOne(targetEntity="Ability", inversedBy="competencyAbilities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $ability;

    /**
     * @ORM\ManyToOne(targetEntity="Level")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $level;

    /**
     * @param Competency $competency
     */
    public function setCompetency(Competency $competency)
    {
        $this->competency = $competency;
    }

    /**
     * @return Competency
     */
    public function getCompetency()
    {
        return $this->competency;
    }

    /**
     * @param Ability $ability
     */
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

    /**
     * @param Level $level
     */
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
