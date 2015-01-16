<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_cursus_model")
 * @ORM\Entity
 */
class CursusModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     */
    protected $code;
    
    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CursusModelCourse",
     *     mappedBy="cursusModel"
     * )
     */
    protected $cursusCourses;
    
    public function __construct()
    {
        $this->cursusCourses = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }
    
    public function getName()
    {   
        return $this->name;
    }

    public function setName($name)
    {   
        $this->name = $name;
    }

    public function getDescription()
    {   
        return $this->description;
    }

    public function setDescription($description)
    {   
        $this->description = $description;
    }

    public function getCursesCourses()
    {
        return $this->cursusCourses->toArray();
    }
}