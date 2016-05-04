<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/23/15
 */

namespace Icap\SocialmediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="icap__socialmedia_wall_item")
 * @ORM\Entity(repositoryClass="Icap\SocialmediaBundle\Repository\WallItemRepository")
 */
class WallItem
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \Icap\SocialmediaBundle\Entity\LikeAction
     *
     * @ORM\ManyToOne(targetEntity="Icap\SocialmediaBundle\Entity\LikeAction")
     * @ORM\JoinColumn(name="like_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $like = null;

    /**
     * @var \Icap\SocialmediaBundle\Entity\ShareAction
     *
     * @ORM\ManyToOne(targetEntity="Icap\SocialmediaBundle\Entity\ShareAction")
     * @ORM\JoinColumn(name="share_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $share = null;

    /**
     * @var \Icap\SocialmediaBundle\Entity\CommentAction
     *
     * @ORM\ManyToOne(targetEntity="Icap\SocialmediaBundle\Entity\CommentAction")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $comment = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @var \Claroline\CoreBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", name="visible")
     */
    protected $visible = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Icap\SocialmediaBundle\Entity\LikeAction
     */
    public function getLike()
    {
        return $this->like;
    }

    /**
     * @param \Icap\SocialmediaBundle\Entity\LikeAction $like
     *
     * @return $this
     */
    public function setLike($like)
    {
        $this->like = $like;

        return $this;
    }

    /**
     * @return \Icap\SocialmediaBundle\Entity\ShareAction
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @param \Icap\SocialmediaBundle\Entity\ShareAction $share
     *
     * @return $this
     */
    public function setShare($share)
    {
        $this->share = $share;

        return $this;
    }

    /**
     * @return \Icap\SocialmediaBundle\Entity\CommentAction
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \Icap\SocialmediaBundle\Entity\CommentAction $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return $this
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }
}
