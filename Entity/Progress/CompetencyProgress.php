<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="HeVinci\CompetencyBundle\Repository\CompetencyProgressRepository")
 * @ORM\Table(name="hevinci_competency_progress")
 */
class CompetencyProgress extends AbstractCompetencyProgress
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
}
