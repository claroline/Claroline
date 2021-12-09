<?php

namespace Claroline\SlideshowBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Slideshow.
 *
 * @ORM\Table(name="claro_slideshow")
 * @ORM\Entity()
 */
class Slideshow extends AbstractResource
{
    /**
     * Auto play the slideshow on open.
     *
     * @ORM\Column(name="auto_play", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $autoPlay = false;

    /**
     * Interval between 2 slides (in ms).
     *
     * @ORM\Column(name="slide_interval", type="integer")
     *
     * @var int
     */
    private $interval = 5000;

    /**
     * Show overview to users or directly start the slideshow.
     *
     * @ORM\Column(name="show_overview", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $showOverview = false;

    /**
     * Show controls to users.
     *
     * @ORM\Column(name="show_controls", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $showControls = false;

    /**
     * Description of the slideshow to be shown on the overview.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * The list of slides in the the slideshow.
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\SlideshowBundle\Entity\Resource\Slide",
     *     mappedBy="slideshow",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var ArrayCollection|Slide[]
     */
    private $slides;

    /**
     * Slideshow constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->slides = new ArrayCollection();
    }

    /**
     * Get auto play.
     */
    public function getAutoPlay(): bool
    {
        return $this->autoPlay;
    }

    /**
     * Set auto play.
     */
    public function setAutoPlay(bool $autoPlay)
    {
        $this->autoPlay = $autoPlay;
    }

    /**
     * Is overview shown ?
     */
    public function getShowOverview(): bool
    {
        return $this->showOverview;
    }

    /**
     * Set show overview.
     */
    public function setShowOverview(bool $showOverview)
    {
        $this->showOverview = $showOverview;
    }

    /**
     * Get interval.
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * Set interval.
     */
    public function setInterval(int $interval)
    {
        $this->interval = $interval;
    }

    /**
     * Are controls shown ?
     */
    public function getShowControls(): bool
    {
        return $this->showControls;
    }

    /**
     * Set show controls.
     */
    public function setShowControls(bool $showControls)
    {
        $this->showControls = $showControls;
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
     * Get slides.
     *
     * @return ArrayCollection
     */
    public function getSlides()
    {
        return $this->slides;
    }

    /**
     * Add a slide to the slideshow.
     */
    public function addSlide(Slide $slide)
    {
        if (!$this->slides->contains($slide)) {
            $this->slides->add($slide);
            $slide->setSlideshow($this);
        }
    }

    /**
     * Remove a slide from the slideshow.
     */
    public function removeSlide(Slide $slide)
    {
        if ($this->slides->contains($slide)) {
            $this->slides->removeElement($slide);
            $slide->setSlideshow(null);
        }
    }
}
