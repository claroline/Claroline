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

use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseRepository")
 * @ORM\Table(name="claro_cursusbundle_course")
 * @DoctrineAssert\UniqueEntity("code")
 */
class Course
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
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @ORM\Column(name="public_registration", type="boolean")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Model\WorkspaceModel"
     * )
     * @ORM\JoinColumn(name="workspace_model_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspaceModel;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     mappedBy="course"
     * )
     */
    protected $sessions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseUser",
     *     mappedBy="course"
     * )
     */
    protected $courseUsers;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseGroup",
     *     mappedBy="course"
     * )
     */
    protected $courseGroups;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->courseUsers = new ArrayCollection();
        $this->courseGroups = new ArrayCollection();
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

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getPublicRegistration()
    {
        return $this->publicRegistration;
    }

    public function setPublicRegistration($publicRegistration)
    {
        $this->publicRegistration = $publicRegistration;
    }

    public function getWorkspaceModel()
    {
        return $this->workspaceModel;
    }

    public function setWorkspaceModel(WorkspaceModel $workspaceModel)
    {
        $this->workspaceModel = $workspaceModel;
    }

    public function getSessions()
    {
        return $this->sessions->toArray();
    }

    public function getCourseUsers()
    {
        return $this->courseUsers->toArray();
    }

    public function getCourseGroups()
    {
        return $this->courseGroups->toArray();
    }
}