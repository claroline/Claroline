<?php

namespace UJM\ExoBundle\Entity\Item;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'ujm_share')]
#[ORM\Entity]
class Shared
{
    /**
     * The user with whom the question is shared.
     *
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    private $user;

    /**
     * The shared question.
     *
     *
     * @var Item
     */
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: \UJM\ExoBundle\Entity\Item\Item::class)]
    private $question;

    /**
     * Gives the user the ability to edit and delete the question.
     *
     *
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $adminRights = false;

    /**
     * Sets user.
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Gets user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets question.
     */
    public function setQuestion(Item $question)
    {
        $this->question = $question;
    }

    /**
     * Gets question.
     *
     * @return Item
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Sets admin right.
     *
     * @param bool $adminRights
     */
    public function setAdminRights($adminRights)
    {
        $this->adminRights = $adminRights;
    }

    /**
     * Does user have admin rights?
     *
     * @return bool
     */
    public function hasAdminRights()
    {
        return $this->adminRights;
    }
}
