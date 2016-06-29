<?php

namespace Innova\PathBundle\Entity;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\TagBundle\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Configuration of Widgets Path.
 *
 * @ORM\Table(name="innova_path_widget_config")
 * @ORM\Entity()
 */
class PathWidgetConfig
{
    /**
     * Unique identified of the Configuration.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Status.
     *
     * @var string
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $status;

    /**
     * Tags.
     *
     * @ORM\ManyToMany(targetEntity="Claroline\TagBundle\Entity\Tag", cascade={"persist", "merge"})
     * @ORM\JoinTable(
     *      name               = "innova_path_widget_config_tags",
     *      joinColumns        = { @ORM\JoinColumn(name="widget_config_id", referencedColumnName="id") },
     *      inverseJoinColumns = { @ORM\JoinColumn(name="tag_id", referencedColumnName="id") }
     * )
     */
    protected $tags;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $widgetInstance;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status.
     *
     * @param $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the list of Tags displayed in the Widget.
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add a Tag.
     *
     * @param \Claroline\TagBundle\Entity\Tag $tag
     *
     * @return $this
     */
    public function addTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove a Tag.
     *
     * @param \Claroline\TagBundle\Entity\Tag $tag
     *
     * @return $this
     */
    public function removeTag(Tag $tag)
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }
}
