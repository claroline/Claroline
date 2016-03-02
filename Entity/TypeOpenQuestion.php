<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\TypeOpenQuestion.
 *
 * @ORM\Entity
 * @ORM\Table(name="ujm_type_open_question")
 */
class TypeOpenQuestion
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var int
     *
     * @ORM\Column(unique=true, name="code", type="integer")
     */
    private $code;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set code.
     *
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code.
     *
     * @return int
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
