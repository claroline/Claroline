<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Activity\ActivityRepository")
 * @ORM\Table(name="claro_activity")
 */
class Activity extends AbstractResource
{
    /**
     * @var string
     * @ORM\Column(length=255, nullable=true)
     */
    protected $title;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="description", type="text")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    protected $primaryResource;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters",
     *     inversedBy="activity",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="parameters_id", onDelete="cascade", nullable=true)
     */
    protected $parameters;

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Activity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return activity
     */
    public function setDescription($description)
    {
        if ($description !== null) {
            $this->description = $description;
        }

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get resource node.
     *
     * @return string
     */
    public function getPrimaryResource()
    {
        return $this->primaryResource;
    }

    /**
     * Set resource node.
     *
     * @param ResourceNode $primaryResource
     *
     * @return activity
     */
    public function setPrimaryResource($primaryResource = null)
    {
        $this->primaryResource = $primaryResource;

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return ActivityParameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters.
     *
     * @param ActivityParameters $parameters
     *
     * @return activity
     */
    public function setParameters(ActivityParameters $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
