<?php

namespace Icap\InwicastBundle\Entity;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Media.
 *
 * @ORM\Table(name="inwicast_plugin_media")
 * @ORM\Entity(repositoryClass="Icap\InwicastBundle\Repository\MediaRepository")
 * @JMS\ExclusionPolicy("none")
 */
class Media
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Exclude
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="mediaRef", type="string", length=255)
     * @JMS\SerializedName("mediaRef")
     */
    protected $mediaRef;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var \DateTime
     */
    protected $date = null;

    /**
     * @ORM\Column(name="preview_url", type="string", nullable=true)
     * @JMS\SerializedName("previewUrl")
     */
    protected $previewUrl = null;

    /**
     * @var int
     */
    protected $views = 0;

    /**
     * @ORM\Column(name="width", type="integer")
     */
    protected $width = 640;

    /**
     * @ORM\Column(name="height", type="integer")
     */
    protected $height = 480;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(name="widgetinstance_id", referencedColumnName="id", unique=true, onDelete="CASCADE")
     * @JMS\Exclude
     **/
    protected $widgetInstance;

    public function __construct(
        $mediaRef = null,
        $title = null,
        $description = null,
        $date = null,
        $previewUrl = null,
        $views = null,
        $width = 640,
        $height = 480
    ) {
        $this->mediaRef = $mediaRef;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->previewUrl = $previewUrl;
        $this->views = $views;
        $this->width = $width;
        $this->height = $height;
    }

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
     * Set code.
     *
     * @param string $mediaRef
     *
     * @return Media
     */
    public function setMediaRef($mediaRef)
    {
        $this->mediaRef = $mediaRef;

        return $this;
    }

    /**
     * Get mediaRef.
     *
     * @return string
     */
    public function getMediaRef()
    {
        return $this->mediaRef;
    }

    /**
     * @param string $title
     *
     * @return Media
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Media
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get image.
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->previewUrl;
    }

    /**
     * @param string $previewUrl
     *
     * @return $this
     */
    public function setPreviewUrl($previewUrl)
    {
        $this->previewUrl = $previewUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     *
     * @return $this
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get widgetInstance.
     *
     * @return WidgetInstance
     */
    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    /**
     * Add widgetInstance.
     *
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $widgetInstance
     *
     * @return Media
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }
}
