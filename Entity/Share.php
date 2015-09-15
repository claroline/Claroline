<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Share
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ShareRepository")
 * @ORM\Table(name="ujm_share")
 */
class Share
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Question")
     */
    private $question;

    /**
     * @var boolean $allowToModify
     *
     * @ORM\Column(name="allowToModify", type="boolean")
     */
    private $allowToModify;


    public function __construct(User $user, Question $question)
    {
        $this->user = $user;
        $this->question = $question;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setQuestion(Question $question)
    {
        $this->question = $question;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set allowToModify
     *
     * @param boolean $allowToModify
     */
    public function setAllowToModify($allowToModify)
    {
        $this->allowToModify = $allowToModify;
    }

    /**
     * Get allowToModify
     *
     * @return boolean
     */
    public function getAllowToModify()
    {
        return $this->allowToModify;
    }
}
