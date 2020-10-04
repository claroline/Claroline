<?php

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractRegistration
{
    use Id;
    use Uuid;

    const TUTOR = 'tutor';
    const LEARNER = 'learner';

    /**
     * @ORM\Column(name="registration_type")
     *
     * @var string
     */
    protected $type = self::LEARNER;

    /**
     * @ORM\Column(name="registration_date", type="datetime")
     *
     * @var \DateTime
     */
    protected $date;

    public function __construct()
    {
        $this->refreshUuid();

        $this->date = new \DateTime();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }
}
