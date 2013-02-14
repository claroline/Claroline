<?php

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_subject")
 */
class Subject extends AbstractResource
{

    /**
     * @ORM\Column(type="string", name="title")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @Assert\NotBlank()
     */
    protected $message;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        return $this->message = $message;
    }
}