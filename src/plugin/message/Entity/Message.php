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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     name="claro_message",
 *     indexes={
 *         @ORM\Index(name="level_idx", columns={"lvl"}),
 *         @ORM\Index(name="root_idx", columns={"root"})
 *     }
 * )
 * @Gedmo\Tree(type="nested")
 */
class Message
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $object;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     * @var string
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
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\MessageBundle\Entity\UserMessage",
     *     mappedBy="message"
     * )
     *
     * @var UserMessage[]|ArrayCollection
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
     *
     * @var Message
     */
    protected $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\MessageBundle\Entity\Message",
     *     mappedBy="parent"
     * )
     * @ORM\OrderBy({"lft" = "ASC"})
     *
     * @var Message[]|ArrayCollection
     */
    protected $children;

    /**
     * @ORM\Column(name="sender_username")
     *
     * @var string
     */
    protected $senderUsername = 'claroline-connect';

    /**
     * @ORM\Column(name="receiver_string", length=16000)
     *
     * @var string
     */
    protected $to;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array
     */
    protected $attachments = [];

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->children = new ArrayCollection();
        $this->userMessages = new ArrayCollection();
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

    /**
     * @return User|null
     */
    public function getSender()
    {
        return $this->user;
    }

    public function setSender(?User $sender = null)
    {
        $this->user = $sender;
        $this->senderUsername = $sender ? $sender->getUsername() : 'claroline-connect';
    }

    public function getCreator()
    {
        return $this->getSender();
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
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    public function getUserMessages()
    {
        return $this->userMessages;
    }

    public function getUserMessage(User $user)
    {
        $found = null;
        foreach ($this->userMessages as $userMessage) {
            if ($user->getUuid() === $userMessage->getUser()->getUuid()) {
                $found = $userMessage;
            }
        }

        return $found;
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

    public function getReceivers()
    {
        $users = [];
        $groups = [];
        $workspaces = [];

        if (!empty($this->to)) {
            $receivers = explode(';', $this->to);
            // split the string of target into different array.
            foreach ($receivers as $receiver) {
                if (!empty($receiver)) {
                    if ('{' === substr($receiver, 0, 1)) {
                        $groups[] = trim($receiver, '{}');
                    } elseif ('[' === substr($receiver, 0, 1)) {
                        $workspaces[] = trim($receiver, '[]');
                    } else {
                        $users[] = $receiver;
                    }
                }
            }
        }

        return [
            'users' => $users,
            'groups' => $groups,
            'workspaces' => $workspaces,
        ];
    }

    public function setReceivers(array $users = [], array $groups = [], array $workspaces = [])
    {
        $receivers = [];

        if (!empty($users)) {
            $receivers = array_merge($users, $receivers);
        }

        if (!empty($groups)) {
            $receivers = array_merge(array_map(function ($group) {
                return '{'.$group.'}';
            }, $groups), $receivers);
        }

        if (!empty($workspaces)) {
            $receivers = array_merge(array_map(function ($workspace) {
                return '['.$workspace.']';
            }, $workspaces), $receivers);
        }

        $receiversString = implode(';', $receivers);

        $this->setTo($receiversString);
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
    }
}
