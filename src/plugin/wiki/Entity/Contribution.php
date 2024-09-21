<?php

namespace Icap\WikiBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'icap__wiki_contribution')]
#[ORM\Entity(repositoryClass: \Icap\WikiBundle\Repository\ContributionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Contribution
{
    use Id;
    use Uuid;

    #[ORM\Column(type: 'string', nullable: true)]
    protected $title;

    #[ORM\Column(type: 'text', nullable: true)]
    protected $text;

    /**
     * @Gedmo\Timestampable(on="create")
     */
    #[ORM\Column(type: 'datetime', name: 'creation_date')]
    protected $creationDate;

    /**
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    protected $contributor;

    #[ORM\JoinColumn(name: 'section_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \Icap\WikiBundle\Entity\Section::class)]
    protected $section;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    public function getTextForPdf()
    {
        $tmpText = $this->text;
        str_replace('&nbsp;', ' ', $tmpText);
        preg_replace('/alt=["\'][a-zA-Z]*["\']/g', '', $tmpText);

        return $tmpText;
    }

    /**
     * @param mixed $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Returns the resource creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Returns the resource creation date.
     *
     * @param $creationDate
     *
     * @return $this
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Set contributor.
     *
     * @param User $contributor
     *
     * @return Contribution
     */
    public function setContributor(User $contributor = null)
    {
        $this->contributor = $contributor;

        return $this;
    }

    /**
     * Get contributor.
     *
     * @return User
     */
    public function getContributor()
    {
        return $this->contributor;
    }

    /**
     * Set section.
     *
     * @param \Icap\WikiBundle\Entity\Section $section
     *
     * @return $this
     */
    public function setSection(Section $section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Get section.
     *
     * @return \Icap\WikiBundle\Entity\Section
     */
    public function getSection()
    {
        return $this->section;
    }
}
