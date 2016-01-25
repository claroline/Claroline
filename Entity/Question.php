<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\QuestionRepository")
 * @ORM\Table(name="ujm_question")
 */
class Question
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $type;

    /**
     * @ORM\Column
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    private $invite;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $feedback;

    /**
     * @ORM\Column(name="date_create", type="datetime")
     */
    private $dateCreate;

    /**
     * @ORM\Column(name="date_modify", type="datetime", nullable=true)
     */
    private $dateModify;

    /**
     * @ORM\Column(type="boolean")
     */
    private $locked = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $model = false;

     /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $supplementary;

     /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $specification;

     /**
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Hint",
     *     mappedBy="question",
     *     cascade={"remove", "persist"}
     * )
     */
    private $hints;

    public function __construct()
    {
        $this->hints = new ArrayCollection();
        $this->dateCreate = new \DateTime();
    }

    /**
     * Note: this method is automatically called in AbstractInteraction#setQuestion
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $invite
     */
    public function setInvite($invite)
    {
        $this->invite = $invite;
    }

    /**
     * @return string
     */
    public function getInvite()
    {
        return $this->invite;
    }

    /**
     * @param string $feedback
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @param \Datetime $dateCreate
     */
    public function setDateCreate(\DateTime $dateCreate)
    {
        $this->dateCreate = $dateCreate;
    }

    /**
     * @return \Datetime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * @param \Datetime $dateModify
     */
    public function setDateModify(\DateTime $dateModify)
    {
        $this->dateModify = $dateModify;
    }

    /**
     * @return \Datetime
     */
    public function getDateModify()
    {
        return $this->dateModify;
    }

    /**
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param boolean $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return boolean
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return ArrayCollection
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * @param Hint $hint
     */
    public function addHint(Hint $hint)
    {
        $this->hints->add($hint);
        $hint->setQuestion($this);
    }

    /**
     * @param array $hints
     */
    public function setHints(array $hints)
    {
        foreach ($hints as $hint) {
            $this->addHint($hint);
        }
    }

    /**
     * @param string $supplementary
     */
    public function setSupplementary($supplementary)
    {
        $this->supplementary = $supplementary;
    }

    /**
     * @return string
     */
    public function getSupplementary()
    {
        return $this->supplementary;
    }

    /**
     * @param string $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

}
