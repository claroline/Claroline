<?php

namespace HeVinci\CompetencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_objective_competency")
 */
class ObjectiveCompetency implements \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="Competency")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $framework;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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

    /**
     * @param Competency $framework
     */
    public function setFramework(Competency $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @return Competency
     */
    public function getFramework()
    {
        return $this->framework;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id
        ];
    }
}
