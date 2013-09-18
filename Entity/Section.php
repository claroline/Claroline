<?php

namespace ICAP\WikiBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__wikibundle_section")
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;



    /**
     * @ORM\ManyToOne(
     *      targetEntity="ICAP\WikiBundle\Entity\Wiki",
     *      inversedBy="sections"
     * )
     * @ORM\JoinColumn(name="wiki_id", referencedColumnName="id", nullable=false)
     */
    protected $wiki;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="ICAP\WikiBundle\Entity\Section",
     *      inversedBy="sections"
     * )
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @ORM\Column(type="datetime", name="created")
     * @Gedmo\Timestampable(on="create")
     */
    protected $creationDate;

    /**
     * @ORM\OneToMany(
     *      targetEntity="ICAP\WikiBundle\Entity\Section",
     *      mappedBy="section",
     *      cascade={"all"},
     *      orphanRemoval=true
     * )
     */
    protected $sections;

    /**
    * Get id
    *
    * @return integer
    */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set wiki
     *
     * @param \ICAP\WikiBundle\Entity\Wiki $wiki
     * @return section
     */
    public function setWiki(\ICAP\WikiBundle\Entity\Wiki $wiki)
    {
        $this->wiki = $wiki;

        return $this;
    }

    /**
     * Get wiki
     *
     * @return \ICAP\WikiBundle\Entity\Wiki
     */
    public function getWiki()
    {
        return $this->wiki;
    }

    /**
     * Set wiki
     *
     * @param \ICAP\WikiBundle\Entity\Wiki $wiki
     * @return section
     */
    public function setParent(\ICAP\WikiBundle\Entity\Section $section)
    {
        $this->section = $section;
        return $this;
    }

    /**
     * Get wiki
     *
     * @return \ICAP\WikiBundle\Entity\Wiki
     */
    public function getParent()
    {
        return $this->parent;
    }


    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        return $this->name = $name;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        return $this->text = $text;
    }

    /**
     * Set sections
     *
     * @param string $description
     * @return section
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * Get section
     *
     * @return string
     */
    public function getSections()
    {
        return $this->sections;
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


}