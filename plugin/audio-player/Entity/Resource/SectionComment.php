<?php

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_audio_resource_section_comment")
 */
class SectionComment
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $content;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\AudioPlayerBundle\Entity\Resource\Section",
     *     inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="section_id", onDelete="CASCADE")
     *
     * @var Section
     */
    protected $section;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="SET NULL")
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     *
     * @var \DateTime
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $editionDate;

    public function __construct()
    {
        $this->refreshUuid();
        $this->creationDate = new \DateTime();
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection(Section $section)
    {
        $this->section = $section;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate()
    {
        return $this->editionDate;
    }

    public function setEditionDate(\DateTime $editionDate = null)
    {
        $this->editionDate = $editionDate;
    }
}
