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
     * @ORM\Column(type="boolean")
     */
    private $view = true;

    public function __construct(Hint $hint, Paper $paper)
    {
        $this->hint = $hint;
        $this->paper = $paper;
    }

    public function setHint(Hint $hint)
    {
        $this->hint = $hint;
    }

    public function getHint()
    {
        return $this->hint;
    }

    public function setPaper(Paper $paper)
    {
        $this->paper = $paper;
    }

    public function getPaper()
    {
        return $this->paper;
    }

    /**
     * @param bool $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return bool
     */
    public function getView()
    {
        return $this->view;
    }
}
