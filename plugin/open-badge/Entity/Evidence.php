<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro__open_badge_evidence")
 */
class Evidence
{
    use UuidTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $narrative;

    /**
     * @ORM\Column()
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(nullable=true)
     */
    private $genre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $audience;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\OpenBadgeBundle\Entity\Assertion")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $assertion;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     *
     * @var ResourceNode
     */
    private $resourceEvidences;

    public function __construct()
    {
        $this->refreshUuid();
        $this->resourceEvidences = new ArrayCollection();
    }

    /**
     * Get the value of Id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Id.
     *
     * @param mixed id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Narrative.
     *
     * @return mixed
     */
    public function getNarrative()
    {
        return $this->narrative;
    }

    /**
     * Set the value of Narrative.
     *
     * @param mixed narrative
     *
     * @return self
     */
    public function setNarrative($narrative)
    {
        $this->narrative = $narrative;

        return $this;
    }

    /**
     * Get the value of Name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of Name.
     *
     * @param mixed name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of Description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description.
     *
     * @param mixed description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Genre.
     *
     * @return mixed
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set the value of Genre.
     *
     * @param mixed genre
     *
     * @return self
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get the value of Audience.
     *
     * @return mixed
     */
    public function getAudience()
    {
        return $this->audience;
    }

    /**
     * Set the value of Audience.
     *
     * @param mixed audience
     *
     * @return self
     */
    public function setAudience($audience)
    {
        $this->audience = $audience;

        return $this;
    }

    /**
     * Get the value of Assertion.
     *
     * @return mixed
     */
    public function getAssertion()
    {
        return $this->assertion;
    }

    /**
     * Set the value of Assertion.
     *
     * @param mixed assertion
     *
     * @return self
     */
    public function setAssertion($assertion)
    {
        $this->assertion = $assertion;

        return $this;
    }

    public function setResourceEvidences(array $nodes)
    {
        $this->resourceEvidences->clear();
        $this->resourceEvidences = $nodes;
    }

    public function getResourceEvidences()
    {
        return $this->resourceEvidences;
    }
}
