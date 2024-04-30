<?php

namespace Claroline\SlideshowBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Display\Color;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * Slide.
 *
 * @ORM\Table(name="claro_slide")
 *
 * @ORM\Entity()
 */
class Slide
{
    use Id;
    use Uuid;
    use Color;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $content;

    /**
     * Order of the slide in the slideshow.
     *
     * @ORM\Column(name="slide_order", type="integer")
     */
    private int $order = 0;

    /**
     * The title of the slide.
     *
     * @ORM\Column(nullable=true)
     */
    private ?string $title;

    /**
     * Description of the slide.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * The parent slideshow.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\SlideshowBundle\Entity\Resource\Slideshow", inversedBy="slides")
     *
     * @ORM\JoinColumn(name="slideshow_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Slideshow $slideshow = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?String
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): void
    {
        $this->order = $order;
    }

    public function getSlideshow(): Slideshow
    {
        return $this->slideshow;
    }

    public function setSlideshow(Slideshow $slideshow = null): void
    {
        $this->slideshow = $slideshow;
    }
}
