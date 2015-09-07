<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Question
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\QuestionRepository")
 * @ORM\Table(name="ujm_question")
 */
class Question
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \Datetime $dateCreate
     *
     * @ORM\Column(name="date_create", type="datetime")
     */
    private $dateCreate;

    /**
     * @var \Datetime $dateModify
     *
     * @ORM\Column(name="date_modify", type="datetime", nullable=true)
     */
    private $dateModify;

    /**
     * @var boolean $locked
     *
     * @ORM\Column(name="locked", type="boolean", nullable=true)
     */
    private $locked;

    /**
     * @var boolean $model
     *
     * @ORM\Column(name="model", type="boolean", nullable=true)
     */
    private $model;

    /**
     * @ORM\ManyToMany(targetEntity="UJM\ExoBundle\Entity\Document")
     * @ORM\JoinTable(
     *     name="ujm_document_question",
     *     joinColumns={
     *         @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     *     }
     * )
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

     /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Category")
     */
    private $category;

    /**
     * Note: used for joins only.
     *
     * @ORM\OneToMany(targetEntity="ExerciseQuestion", mappedBy="question")
     */
    private $exerciseQuestions;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->setLocked(false);
        $this->setModel(false);
    }

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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateCreate
     *
     * @param \Datetime $dateCreate
     */
    public function setDateCreate(\DateTime $dateCreate)
    {
        $this->dateCreate = $dateCreate;
    }

    /**
     * Get dateCreate
     *
     * @return \Datetime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * Set dateModify
     *
     * @param \Datetime $dateModify
     */
    public function setDateModify(\DateTime $dateModify)
    {
        $this->dateModify = $dateModify;
    }

    /**
     * Get dateModify
     *
     * @return \Datetime
     */
    public function getDateModify()
    {
        return $this->dateModify;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * Get locked
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set model
     *
     * @param boolean $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Get model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Gets an array of Documents.
     *
     * @return array An array of Documents objects
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add document
     *
     * @param Document $document
     */
    public function addDocument(Document $document)
    {
        $this->document[] = $document;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }
}
