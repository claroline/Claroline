<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\MessageBundle\Repository\MessageRepository")
 * @ORM\Table(
 *     name="claro_message",
 *     indexes={
 *         @Index(name="level_idx", columns={"lvl"}),
 *         @Index(name="root_idx", columns={"root"})
 *     }
 * )
 * @Gedmo\Tree(type="nested")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Groups({"api_message"})
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @JMS\Groups({"api_message"})
     */
    protected $object;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @JMS\Groups({"api_message"})
     */
    protected $content;

    /**
     * @todo rename the property to "sender"
     *
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="sender_id", onDelete="CASCADE", nullable=true)
     * @JMS\Groups({"api_message"})
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @JMS\Groups({"api_message"})
     */
    protected $date;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    protected $isRemoved;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\MessageBundle\Entity\UserMessage",
     *     mappedBy="message"
     * )
     */
    protected $userMessages;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\MessageBundle\Entity\Message",
     *     inversedBy="children"
     * )
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\MessageBundle\Entity\Message",
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\Column(name="sender_username")
     * @JMS\Groups({"api_message"})
     */
    protected $senderUsername = 'claroline-connect';

    /**
     * @ORM\Column(name="receiver_string", length=16000)
     * @Assert\Length(max = "16000")
     * @JMS\Groups({"api_message"})
     */
    protected $to;

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

    public function getSender()
    {
        return $this->user;
    }

    public function setSender($sender)
    {
        $this->user = $sender;
        $this->senderUsername = ($sender) ? $sender->getUsername() : 'claroline-connect';
    }

    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets the message creation date.
     *
     * NOTE : creation date is already handled by the timestamp listener; this
     *        setter exists mainly for testing purposes.
     *
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
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

    public function getSenderUsername()
    {
        return $this->senderUsername;
    }
}
