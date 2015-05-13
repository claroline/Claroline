<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_competency_progress_log")
 */
class CompetencyProgressLog extends AbstractCompetencyProgress
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
