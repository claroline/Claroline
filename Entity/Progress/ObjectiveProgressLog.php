<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_objective_progress_log")
 */
class ObjectiveProgressLog extends AbstractObjectiveProgress
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
