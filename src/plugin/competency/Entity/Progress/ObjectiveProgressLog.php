<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_objective_progress_log")
 */
class ObjectiveProgressLog extends AbstractObjectiveProgress
{
    use Uuid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function __construct()
    {
        $this->refreshUuid();
    }

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
