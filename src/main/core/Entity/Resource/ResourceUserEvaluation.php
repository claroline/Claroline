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

use Claroline\EvaluationBundle\Entity\Evaluation\AbstractUserEvaluation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Represents the global evaluation of a User for a ResourceNode.
 * There is only one for a user and a resource.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_resource_user_evaluation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="resource_user_evaluation",
 *             columns={"resource_node", "user_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"resourceNode", "user"})
 */
class ResourceUserEvaluation extends AbstractUserEvaluation
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(name="resource_node", onDelete="CASCADE")
     *
     * @var ResourceNode
     */
    protected $resourceNode;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceEvaluation",
     *     mappedBy="resourceUserEvaluation"
     * )
     *
     * @var ResourceEvaluation[]|ArrayCollection
     */
    protected $evaluations;

    /**
     * @ORM\Column(name="nb_attempts", type="integer")
     *
     * @var int
     */
    protected $nbAttempts = 0;

    /**
     * @ORM\Column(name="nb_openings", type="integer")
     *
     * @var int
     */
    protected $nbOpenings = 0;

    /**
     * Is the evaluation used to compute the workspace evaluation ?
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $required = false;

    /**
     * ResourceUserEvaluation constructor.
     */
    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getEvaluations()
    {
        return $this->evaluations->toArray();
    }

    public function getNbAttempts()
    {
        return $this->nbAttempts ?? 0;
    }

    public function setNbAttempts($nbAttempts)
    {
        $this->nbAttempts = $nbAttempts;
    }

    public function getNbOpenings()
    {
        return $this->nbOpenings ?? 0;
    }

    public function setNbOpenings($nbOpenings)
    {
        $this->nbOpenings = $nbOpenings;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function setRequired($required)
    {
        $this->required = $required;
    }
}
