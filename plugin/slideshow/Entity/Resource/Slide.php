<?php

namespace Claroline\SlideshowBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Color;
use Doctrine\ORM\Mapping as ORM;

/**
 * Slide.
 *
 * @ORM\Table(name="claro_slide")
 * @ORM\Entity()
 */
class Slide
{
    use Id;
    use Uuid;

    use Color;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $content;

    /**
     * Order of the slide in the slideshow.
     *
     * @ORM\Column(name="slide_order", type="integer")
     *
     * @var int
     */
    private $order;

    /**
     * The title of the slide.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $title;

    /**
     * Description of the slide.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * The parent slideshow.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\SlideshowBundle\Entity\Resource\Slideshow", inversedBy="slides")
     * @ORM\JoinColumn(name="slideshow_id", referencedColumnName="id")
     *
     * @var Slideshow
     */
    private $slideshow;

    /**
     * Slide constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get mime type.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Set mime type.
     *
     * @param string $mimeType
     */
    public function setMimeType(string $mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent(string $content)
    {
        $this->content = $content;
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
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrder(int $order)
    {
        $this->order = $order;
    }

    /**
     * Get parent slideshow.
     *
     * @return Slideshow
     */
    public function getSlideshow(): Slideshow
    {
        return $this->slideshow;
    }

    /**
     * Set parent slideshow.
     *
     * @param Slideshow $slideshow
     */
    public function setSlideshow(Slideshow $slideshow = null)
    {
        $this->slideshow = $slideshow;
    }
}
