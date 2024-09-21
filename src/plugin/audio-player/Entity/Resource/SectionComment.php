<?php

namespace Claroline\AudioPlayerBundle\Entity\Resource;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_audio_resource_section_comment')]
#[ORM\Entity]
class SectionComment
{
    use Id;
    use Uuid;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $content;

    /**
     *
     * @var Section
     */
    #[ORM\JoinColumn(name: 'section_id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Claroline\AudioPlayerBundle\Entity\Resource\Section::class, inversedBy: 'comments')]
    protected $section;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    protected $user;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'creation_date', type: 'datetime')]
    protected $creationDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'edition_date', type: 'datetime', nullable: true)]
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
