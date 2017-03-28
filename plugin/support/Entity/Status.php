<?php

namespace FormaLibre\SupportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="formalibre_support_status")
 * @ORM\Entity(repositoryClass="FormaLibre\SupportBundle\Repository\StatusRepository")
 * @DoctrineAssert\UniqueEntity("code")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Status
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="status_name", unique=true)
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $code;

    /**
     * @ORM\Column(name="status_order", type="integer")
     * @Assert\NotBlank()
     */
    protected $order = 1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    protected $locked = false;

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

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }
}
