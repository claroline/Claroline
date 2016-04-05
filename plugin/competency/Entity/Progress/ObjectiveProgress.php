<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_objective_progress")
 */
class ObjectiveProgress extends AbstractObjectiveProgress
{
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $date;

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Creates a "log" copy of the current instance.
     *
     * @return CompetencyProgress
     */
    public function makeLog()
    {
        $log = new ObjectiveProgressLog();
        $log->setObjective($this->objective);
        $log->setUser($this->user);
        $log->setPercentage($this->percentage);
        $log->setDate($this->date);

        return $log;
    }
}
