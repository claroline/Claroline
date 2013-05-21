<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\TextRepository")
 * @ORM\Table(name="claro_text")
 */
class Text extends AbstractResource
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $version;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Revision",
     *     mappedBy="text",
     *     cascade={"persist"}
     * )
     * @ORM\OrderBy({"version" = "DESC"})
     */
    protected $revisions;

    /** @var string */
    protected $text;

    public function __construct()
    {
        $this->version = 1;
        $this->revisions = new ArrayCollection();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getRevisions()
    {
        return $this->revisions;
    }

    public function addRevision($revision)
    {
        $this->revisions->add($revision);
    }

    public function removeUser($revision)
    {
        $this->revisions->removeElement($revision);
    }

    /**
     * Required for the formtype
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Required for the formtype
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}