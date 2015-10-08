<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Paper.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\PaperRepository")
 * @ORM\Table(name="ujm_paper")
 */
class Paper
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="num_paper", type="integer")
     */
    private $numPaper;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="start", type="datetime")
     */
    private $start;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var text
     *
     * @ORM\Column(name="ordre_question", type="text", nullable=true)
     */
    private $ordreQuestion;

    /**
     * @var bool
     *
     * @ORM\Column(name="archive", type="boolean", nullable=true)
     */
    private $archive;

    /**
     * @var date
     *
     * @ORM\Column(name="date_archive", type="date", nullable=true)
     */
    private $dateArchive;

    /**
     * @var bool
     *
     * @ORM\Column(name="interupt", type="boolean", nullable=true)
     */
    private $interupt;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Exercise")
     */
    private $exercise;

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
     * Set numPaper.
     *
     * @param int $numPaper
     */
    public function setNumPaper($numPaper)
    {
        $this->numPaper = $numPaper;
    }

    /**
     * Get numPaper.
     *
     * @return int
     */
    public function getNumPaper()
    {
        return $this->numPaper;
    }

    /**
     * Set start.
     *
     * @param \Datetime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * Get start.
     *
     * @return \Datetime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end.
     *
     * @param \Datetime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * Get end.
     *
     * @return \Datetime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set ordreQuestion.
     *
     * @param text $ordreQuestion
     */
    public function setOrdreQuestion($ordreQuestion)
    {
        $this->ordreQuestion = $ordreQuestion;
    }

    /**
     * Get ordreQuestion.
     *
     * @return text
     */
    public function getOrdreQuestion()
    {
        return $this->ordreQuestion;
    }

    /**
     * Set archive.
     *
     * @param bool $archive
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
    }

    /**
     * Get archive.
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set dateArchive.
     *
     * @param date $dateArchive
     */
    public function setDateArchive($dateArchive)
    {
        $this->dateArchive = $dateArchive;
    }

    /**
     * Get dateArchive.
     *
     * @return date
     */
    public function getDateArchive()
    {
        return $this->dateArchive;
    }

    /**
     * Set interupt.
     *
     * @param bool $interupt
     */
    public function setInterupt($interupt)
    {
        $this->interupt = $interupt;
    }

    /**
     * Get interupt.
     */
    public function getInterupt()
    {
        return $this->interupt;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    public function getExercise()
    {
        return $this->exercise;
    }

    public function setExercise(\UJM\ExoBundle\Entity\Exercise $exercise)
    {
        $this->exercise = $exercise;
    }
}
