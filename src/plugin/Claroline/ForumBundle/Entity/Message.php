<?php

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\ForumBundle\Entity\Subject;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_message")
 * @ORM\Entity(repositoryClass="Claroline\ForumBundle\Repository\MessageRepository")
 */
class Message extends AbstractResource
{
    /**
     * @ORM\Column(type="string", name="content")
     * @Assert\NotBlank()
     */
    protected $content;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}