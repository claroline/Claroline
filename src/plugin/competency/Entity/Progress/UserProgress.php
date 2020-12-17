<?php

namespace HeVinci\CompetencyBundle\Entity\Progress;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="hevinci_user_progress")
 */
class UserProgress extends AbstractUserProgress
{
    use Uuid;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $date;

    public function __construct()
    {
        $this->refreshUuid();
    }

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
        $log = new UserProgressLog();
        $log->setUser($this->user);
        $log->setPercentage($this->percentage);
        $log->setDate($this->date);

        return $log;
    }
}
