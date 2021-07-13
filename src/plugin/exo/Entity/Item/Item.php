<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ItemRepository")
 * @ORM\Table(name="ujm_question")
 * @ORM\HasLifecycleCallbacks()
 */
class Item
{
    use Id;
    use Uuid;

    /**
     * The mime type of the Item type.
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
     * @ORM\Column(name="invite", type="text", nullable=true)
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
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $description;

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
     * @ORM\OneToMany(targetEntity="ItemObject", mappedBy="question", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"order"="ASC"})
     *
     * @var ArrayCollection
     */
    private $objects;

    /**
     * A list of additional Resources that can help to answer the question.
     *
     * @ORM\OneToMany(targetEntity="ItemResource", mappedBy="question", cascade={"remove", "persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"order"="ASC"})
     *
     * @var ArrayCollection
     */
    private $resources;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $scoreRule;

    /**
     * The linked interaction entity.
     * This is populated by Doctrine Lifecycle events.
     *
     * @var AbstractItem
     */
    private $interaction = null;

    /**
     * Allows other user to edit a question.
     *
     * @ORM\Column(name="protect_update", type="boolean")
     *
     * @var bool
     */
    private $protectUpdate = false;

    /**
     * The is answer mandatory to continue the quiz.
     *
     * @ORM\Column(name="mandatory", type="boolean")
     *
     * @var bool
     * @deprecated. Moved on StepQuestion.
     */
    private $mandatory = false;

    /**
     * @ORM\Column(name="expected_answers", type="boolean")
     *
     * @var bool
     */
    private $expectedAnswers = true;

    /**
     * Item constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->hints = new ArrayCollection();
        $this->objects = new ArrayCollection();
        $this->resources = new ArrayCollection();
        $this->dateCreate = new \DateTime();
        $this->dateModify = new \DateTime();
    }

    /**
     * NB. This is required to make Tags work properly.
     *
     * @return string
     */
    public function __toString()
    {
        if (!empty($this->getTitle())) {
            return $this->getTitle();
        }

        return substr(strip_tags($this->content), 0, 50);
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
     * Gets content without html.
     *
     * @return string
     */
    public function getContentText()
    {
        return strip_tags($this->content);
    }

    /**
     * @return ArrayCollection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    public function addObject(ItemObject $object)
    {
        if (!$this->objects->contains($object)) {
            $this->objects->add($object);
            $object->setQuestion($this);
        }
    }

    public function removeObject(ItemObject $object)
    {
        if ($this->objects->contains($object)) {
            $this->objects->removeElement($object);
            $object->setQuestion(null);
        }
    }

    public function emptyObjects()
    {
        $this->objects->clear();
    }

    /**
     * @return ArrayCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    public function addResource(ItemResource $resource)
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setQuestion($this);
        }
    }

    public function removeResource(ItemResource $resource)
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
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return ArrayCollection
     */
    public function getHints()
    {
        return $this->hints;
    }

    public function addHint(Hint $hint)
    {
        if (!$this->hints->contains($hint)) {
            $this->hints->add($hint);
            $hint->setQuestion($this);
        }
    }

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
     * @return AbstractItem
     */
    public function getInteraction()
    {
        return $this->interaction;
    }

    public function setInteraction(AbstractItem $interaction)
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

    public function setProtectUpdate($protectUpdate)
    {
        $this->protectUpdate = $protectUpdate;
    }

    public function getProtectUpdate()
    {
        return $this->protectUpdate;
    }

    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;
    }

    public function isMandatory()
    {
        return $this->mandatory;
    }

    public function getMandatory()
    {
        return $this->isMandatory();
    }

    /**
     * @return bool
     */
    public function hasExpectedAnswers()
    {
        return $this->expectedAnswers;
    }

    /**
     * @param bool $expectedAnswers
     */
    public function setExpectedAnswers($expectedAnswers)
    {
        $this->expectedAnswers = $expectedAnswers;
    }
}
