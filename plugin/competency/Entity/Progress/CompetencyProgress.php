<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\CompetencyProgressRepository")
 * @ORM\Table(name="hevinci_competency_progress")
 */
class CompetencyProgress extends AbstractCompetencyProgress implements \JsonSerializable
{
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $date;

    /**
     * @ORM\Column(type="integer", name="resource_id", nullable=true)
     */
    protected $resourceId;

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * Creates a "log" copy of the current instance.
     *
     * @return CompetencyProgress
     */
    public function makeLog()
    {
        $log = new CompetencyProgressLog();
        $log->setCompetency($this->competency);
        $log->setUser($this->user);
        $log->setPercentage($this->percentage);
        $log->setDate($this->date);

        if ($this->level) {
            $log->setLevel($this->level);
        }

        return $log;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'percentage' => $this->percentage,
            'competencyName' => $this->competencyName,
            'userName' => $this->userName,
            'levelName' => $this->levelName,
            'date' => $this->date,
            'level' => $this->level,
            'resourceId' => $this->resourceId,
        ];
    }
}
