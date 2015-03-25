<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_objective_competency")
 */
class ObjectiveCompetency
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Objective", inversedBy="objectiveCompetencies")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $objective;

    /**
     * @ORM\ManyToOne(targetEntity="Competency")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $competency;


    /**
     * @ORM\ManyToOne(targetEntity="Level")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $level;

    /**
     * @param Objective $objective
     */
    public function setObjective(Objective $objective)
    {
        $this->objective = $objective;
    }

    /**
     * @return Objective
     */
    public function getObjective()
    {
        return $this->objective;
    }

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
