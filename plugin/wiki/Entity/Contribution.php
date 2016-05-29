<?php

namespace Icap\WikiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Icap\NotificationBundle\Entity\UserPickerContent;

/**
 * @ORM\Entity(repositoryClass="Icap\WikiBundle\Repository\ContributionRepository")
 * @ORM\EntityListeners({"Icap\WikiBundle\Listener\ContributionListener"})
 * @ORM\Table(name="icap__wiki_contribution")
 * @ORM\HasLifecycleCallbacks()
 */
class Contribution
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    protected $textForPdf;

    /**
     * @ORM\Column(type="datetime", name="creation_date")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $contributor;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\WikiBundle\Entity\Section")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $section;

    protected $userPicker = null;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     */
    public function setTitle($title)
    {
        return $this->title = $title;
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
     */
    public function setText($text)
    {
        return $this->text = $text;
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
     * @return \DateTime
     */
    public function setCreationDate($creationDate)
    {
        return $this->creationDate = $creationDate;
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
     * @return contribution
     */
    public function setSection(\Icap\WikiBundle\Entity\Section $section)
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

    /**
     * @param UserPickerContent $userPicker
     *
     * @return $this
     */
    public function setUserPicker(UserPickerContent $userPicker)
    {
        $this->userPicker = $userPicker;

        return $this;
    }

    /**
     * @return \Icap\NotificationBundle\Entity\UserPickerContent
     */
    public function getUserPicker()
    {
        return $this->userPicker;
    }

    /**
     * @ORM\PrePersist
     */
    public function createUserPicker(LifecycleEventArgs $event)
    {
        if ($this->getText() != null) {
            $userPicker = new UserPickerContent($this->getText());
            $this->setUserPicker($userPicker);
            $this->setText($userPicker->getFinalText());
        }
    }
}
