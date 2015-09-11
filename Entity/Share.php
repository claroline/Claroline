<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Share.
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
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Question")
     */
    private $question;

    /**
     * @var bool
     *
     * @ORM\Column(name="allowToModify", type="boolean")
     */
    private $allowToModify;

    public function __construct(\Claroline\CoreBundle\Entity\User $user, \UJM\ExoBundle\Entity\Question $question)
    {
        $this->user = $user;
        $this->question = $question;
    }

    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setQuestion(\UJM\ExoBundle\Entity\Question $question)
    {
        $this->question = $question;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set allowToModify.
     *
     * @param bool $allowToModify
     */
    public function setAllowToModify($allowToModify)
    {
        $this->allowToModify = $allowToModify;
    }

    /**
     * Get allowToModify.
     *
     * @return bool
     */
    public function getAllowToModify()
    {
        return $this->allowToModify;
    }
}
