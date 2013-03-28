<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Validator\Constraints as CustomAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\MessageRepository")
 * @ORM\Table(name="claro_message")
 * @Gedmo\Tree(type="nested")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="object")
     * @Assert\NotBlank()
     */
    protected $object;

    /**
     * @ORM\Column(type="string", name="content")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime", name="date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $date;

    /**
     * @ORM\Column(type="boolean", name="is_removed")
     */
    protected $isRemoved;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\UserMessage",
     *     mappedBy="message"
     * )
     */
    protected $userMessages;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Message",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(
     *     name="parent_id",
     *     referencedColumnName="id",
     *     onDelete="SET NULL"
     * )
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Message",
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\Column(type="string", name="sender_username")
     */
    protected $senderUsername;

    /**
     * @ORM\Column(type="string", name="receiver_username")
     */
    protected $receiverUsername;

    public function __construct()
    {
        $this->isRemoved = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function isRemoved()
    {
        return $this->isRemoved;
    }

    public function markAsRemoved()
    {
        $this->isRemoved = true;
    }

    public function markAsUnremoved()
    {
        $this->isRemoved = false;
    }

    public function getUserMessages()
    {
        return $this->userMessages;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function setSenderUsername($senderUsername)
    {
        $this->senderUsername = $senderUsername;
    }

    public function getSenderUsername()
    {
        return $this->senderUsername;
    }

    public function setReceiverUsername($receiverUsername)
    {
        $this->receiverUsername = $receiverUsername;
    }

    public function getReceiverUsername()
    {
        return $this->receiverUsername;
    }
}