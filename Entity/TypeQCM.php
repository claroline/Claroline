<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\TypeQCM
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\TypeQCMRepository")
 * @ORM\Table(name="ujm_type_qcm")
 */
class TypeQCM
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;
    
    /**
     * @var integer $code
     *
     * @ORM\Column(unique=true, name="code", type="integer")
     */
    private $code;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Set code
     *
     * @param integer $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    public function __toString()
    {
        return $this->value;
    }
}
