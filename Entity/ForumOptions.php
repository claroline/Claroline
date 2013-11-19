<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_options")
 */
class ForumOptions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="subjects", type="integer")
     * @Assert\Range(
     *      min = 5,
     *      max = 50
     * )
     */
    protected $subjects;

    /**
     * @ORM\Column(name="messages", type="integer")
     * @Assert\Range(
     *      min = 5,
     *      max = 100
     * )
     */
    protected $messages;

    public function getId()
    {
        return $this->id;
    }

    public function setSubjects($subjects)
    {
        $this->subjects = $subjects;
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }


}