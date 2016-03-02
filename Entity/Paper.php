<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\PaperRepository")
 * @ORM\Table(name="ujm_paper")
 */
class Paper
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="num_paper", type="integer")
     */
    private $numPaper;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(name="ordre_question", type="text", nullable=true)
     */
    private $ordreQuestion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archive = false;

    /**
     * @ORM\Column(name="date_archive", type="date", nullable=true)
     */
    private $dateArchive;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $interupt = true;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Exercise")
     */
    private $exercise;

    public function __construct()
    {
        $this->start = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $numPaper
     */
    public function setNumPaper($numPaper)
    {
        $this->numPaper = $numPaper;
    }

    /**
     * @return int
     */
    public function getNumPaper()
    {
        return $this->numPaper;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;
    }

    /**
     * @return \Datetime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \Datetime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return \Datetime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param string $ordreQuestion
     */
    public function setOrdreQuestion($ordreQuestion)
    {
        $this->ordreQuestion = $ordreQuestion;
    }

    /**
     * @return string
     */
    public function getOrdreQuestion()
    {
        return $this->ordreQuestion;
    }

    /**
     * @param bool $archive
     */
    public function setArchive($archive)
    {
        $this->archive = $archive;
    }

    /**
     * @return bool
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * @param \DateTime $dateArchive
     */
    public function setDateArchive($dateArchive)
    {
        $this->dateArchive = $dateArchive;
    }

    /**
     * @return \DateTime
     */
    public function getDateArchive()
    {
        return $this->dateArchive;
    }

    /**
     * @param bool $interupt
     */
    public function setInterupt($interupt)
    {
        $this->interupt = $interupt;
    }

    /**
     * @return bool
     */
    public function getInterupt()
    {
        return $this->interupt;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * @param Exercise $exercise
     */
    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
    }
}
