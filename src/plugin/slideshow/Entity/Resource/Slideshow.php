<?php

namespace Claroline\SlideshowBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Slideshow.
 */
#[ORM\Table(name: 'claro_slideshow')]
#[ORM\Entity]
class Slideshow extends AbstractResource
{
    /**
     * Autoplay the slideshow on open.
     */
    #[ORM\Column(name: 'auto_play', type: 'boolean', options: ['default' => 0])]
    private bool $autoPlay = false;

    /**
     * Interval between 2 slides (in ms).
     */
    #[ORM\Column(name: 'slide_interval', type: 'integer')]
    private int $interval = 5000;

    /**
     * Show overview to users or directly start the slideshow.
     */
    #[ORM\Column(name: 'show_overview', type: 'boolean', options: ['default' => 0])]
    private bool $showOverview = false;

    /**
     * Show controls to users.
     */
    #[ORM\Column(name: 'show_controls', type: 'boolean', options: ['default' => 0])]
    private bool $showControls = false;

    /**
     * Description of the slideshow to be shown on the overview.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * The list of slides in the slideshow.
     */
    #[ORM\OneToMany(targetEntity: \Claroline\SlideshowBundle\Entity\Resource\Slide::class, mappedBy: 'slideshow', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $slides;

    public function __construct()
    {
        parent::__construct();

        $this->slides = new ArrayCollection();
    }

    public function getAutoPlay(): bool
    {
        return $this->autoPlay;
    }

    public function setAutoPlay(bool $autoPlay): void
    {
        $this->autoPlay = $autoPlay;
    }

    public function getShowOverview(): bool
    {
        return $this->showOverview;
    }

    public function setShowOverview(bool $showOverview): void
    {
        $this->showOverview = $showOverview;
    }

    public function getInterval(): int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): void
    {
        $this->interval = $interval;
    }

    public function getShowControls(): bool
    {
        return $this->showControls;
    }

    public function setShowControls(bool $showControls): void
    {
        $this->showControls = $showControls;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description = null): void
    {
        $this->description = $description;
    }

    public function getSlides(): Collection
    {
        return $this->slides;
    }

    public function addSlide(Slide $slide): void
    {
        if (!$this->slides->contains($slide)) {
            $this->slides->add($slide);
            $slide->setSlideshow($this);
        }
    }

    public function removeSlide(Slide $slide): void
    {
        if ($this->slides->contains($slide)) {
            $this->slides->removeElement($slide);
            $slide->setSlideshow(null);
        }
    }
}
