<?php

namespace Claroline\ForumBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\ForumBundle\Entity\Subject;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_forum_message")
 */
class Message extends AbstractResource
{
    /**
     * @ORM\Column(type="string", name="content")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ForumBundle\Entity\Subject", inversedBy="messages", cascade={"persist"})
     * @ORM\JoinColumn(name="forum_subject_id", referencedColumnName="id")
     */
    protected $subject;

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject)
    {
        $this->subject = $subject;
    }

}