<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\ChatBundle\Repository\ChatUserRepository")
 * @ORM\Table(name="claro_chatbundle_chat_user")
 */
class ChatUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="chat_username", unique=true)
     * @Assert\NotBlank()
     */
    protected $chatUsername;

    /**
     * @ORM\Column(name="chat_password")
     * @Assert\NotBlank()
     */
    protected $chatPassword;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $options;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getChatUsername()
    {
        return $this->chatUsername;
    }

    public function setChatUsername($chatUsername)
    {
        $this->chatUsername = $chatUsername;
    }

    public function getChatPassword()
    {
        return $this->chatPassword;
    }

    public function setChatPassword($chatPassword)
    {
        $this->chatPassword = $chatPassword;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }
}
