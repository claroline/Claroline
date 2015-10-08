<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\LinkHintPaper.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\LinkHintPaperRepository")
 * @ORM\Table(name="ujm_link_hint_paper")
 */
class LinkHintPaper
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Hint")
     */
    private $hint;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Paper")
     */
    private $paper;

    /**
     * @var bool
     *
     * @ORM\Column(name="view", type="boolean")
     */
    private $view;

    public function __construct(\UJM\ExoBundle\Entity\Hint $hint, \UJM\ExoBundle\Entity\Paper $paper)
    {
        $this->hint = $hint;
        $this->paper = $paper;
    }

    public function setHint(\UJM\ExoBundle\Entity\Hint $hint)
    {
        $this->hint = $hint;
    }

    public function getHint()
    {
        return $this->hint;
    }

    public function setPaper(\UJM\ExoBundle\Entity\Paper $paper)
    {
        $this->paper = $paper;
    }

    public function getPaper()
    {
        return $this->paper;
    }

    /**
     * Set view.
     *
     * @param bool $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Get view.
     */
    public function getView()
    {
        return $this->view;
    }
}
