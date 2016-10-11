<?php

namespace Claroline\CursusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_cursusbundle_presence_status")
 * @ORM\Entity
 */
class PresenceStatus
{
    const NONE = 0;
    const JUSTIFIED = 1;
    const UNJUSTIFIED = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $name;

    /**
     * @ORM\Column(name="presence_type", type="integer", nullable=false)
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $type = self::NONE;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}
