<?php

namespace UJM\ExoBundle\Entity\Question;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use UJM\ExoBundle\Entity\QuestionType\AbstractQuestion;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\QuestionRepository")
 * @ORM\Table(name="ujm_question")
 * @ORM\EntityListeners({"UJM\ExoBundle\Listener\Entity\QuestionListener"})
 * @ORM\HasLifecycleCallbacks()
 */
class Question
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column("uuid", type="string", length=36, unique=true)
     *
     * @var string
     */
    private $uuid;

    /**
     * The mime type of the Question type.
     *
     * @ORM\Column("mime_type", type="string")
     *
     * @var string
     */
    private $mimeType;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="invite", type="text")
     *
     * @var string
     */
    private $content;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $feedback;

    /**
     * The creation date of the question.
     *
     * @ORM\Column(name="date_create", type="datetime")
     *
     * @var \DateTime
     */
    private $dateCreate;

    /**
     * The last update date of the question.
     *
     * @ORM\Column(name="date_modify", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $dateModify;

    /**
     * A model can not be directly linked to an exercise.
     * It needs to be duplicated first to keep the original question untouched.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $model = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question\Category")
     *
     * @var Category
     */
    private $category;

    /**
     * The user who have created the question.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var User
     */
    private $creator;

    /**
     * @ORM\OneToMany(targetEntity="Hint", mappedBy="question", cascade={"remove", "persist"}, orphanRemoval=true)
     *
     * @var ArrayCollection
     */
    private $hints;

    /**
     * @ORM\OneToMany(targetEntity="QuestionObject", mappedBy="question", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"order"="ASC"})
     *
     * @var ArrayCollection
     */
    private $objects;

    /**
     * A list of additional Resources that can help to answer the question.
     *
     * @ORM\OneToMany(targetEntity="QuestionResource", mappedBy="question", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"order"="ASC"})
     *
     * @var ArrayCollection
     */
    private $resources;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $scoreRule;

    /**
     * The linked interaction entity.
     * This is populated by Doctrine Lifecycle events.
     *
     * @var AbstractQuestion
     */
    private $interaction = null;

    /**
     * Question constructor.
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
        $this->hints = new ArrayCollection();
        $this->objects = new ArrayCollection();
        $this->resources = new ArrayCollection();
        $this->dateCreate = new \DateTime();
        $this->dateModify = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets UUID.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Sets UUID.
     *
     * @param $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Gets mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Sets mime type.
     *
     * @param $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
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
     * Sets content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Gets content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return ArrayCollection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param QuestionObject $object
     */
    public function addObject(QuestionObject $object)
    {
        if (!$this->objects->contains($object)) {
            $this->objects->add($object);
            $object->setQuestion($this);
        }
    }

    /**
     * @param QuestionObject $object
     */
    public function removeObject(QuestionObject $object)
    {
        if ($this->objects->contains($object)) {
            $this->objects->removeElement($object);
            $object->setQuestion(null);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param QuestionResource $resource
     */
    public function addResource(QuestionResource $resource)
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setQuestion($this);
        }
    }

    /**
     * @param QuestionResource $resource
     */
    public function removeResource(QuestionResource $resource)
    {
        if ($this->resources->contains($resource)) {
            $this->resources->removeElement($resource);
            $resource->setQuestion(null);
        }
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
        return $this->feedback ?: '';
    }

    /**
     * @ORM\PrePersist
     */
    public function updateDateCreate()
    {
        if (empty($this->dateCreate)) {
            $this->dateCreate = new \DateTime();
        }
    }

    /**
     * @return \Datetime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateDateModify()
    {
        $this->dateModify = new \DateTime();
    }

    /**
     * @return \Datetime
     */
    public function getDateModify()
    {
        return $this->dateModify;
    }

    /**
     * @param bool $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return bool
     */
    public function isModel()
    {
        return $this->model;
    }

    /**
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     */
    public function setCreator(User $creator)
    {
        $this->creator = $creator;
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
    public function setCategory(Category $category = null)
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
        if (!$this->hints->contains($hint)) {
            $this->hints->add($hint);
            $hint->setQuestion($this);
        }
    }

    /**
     * @param Hint $hint
     */
    public function removeHint(Hint $hint)
    {
        if ($this->hints->contains($hint)) {
            $this->hints->removeElement($hint);
        }
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
        return $this->description ?: '';
    }

    /**
     * @return AbstractQuestion
     */
    public function getInteraction()
    {
        return $this->interaction;
    }

    /**
     * @param AbstractQuestion $interaction
     */
    public function setInteraction(AbstractQuestion $interaction)
    {
        $this->interaction = $interaction;
    }

    /**
     * @return string
     */
    public function getScoreRule()
    {
        return $this->scoreRule;
    }

    /**
     * @param string $scoreRule
     *
     * @return string
     */
    public function setScoreRule($scoreRule)
    {
        $this->scoreRule = $scoreRule;

        return $this->scoreRule;
    }
}
