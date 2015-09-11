<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class AbstractInteraction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Question")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $question;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
        $question->setType(get_class($this));
    }

    public function getQuestion()
    {
        return $this->question;
    }
}
