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

use Claroline\EvaluationBundle\Entity\AbstractUserEvaluation;
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
    private $resourceNode;

    /**
     * @ORM\Column(name="nb_attempts", type="integer")
     *
     * @var int
     */
    private $nbAttempts = 0;

    /**
     * @ORM\Column(name="nb_openings", type="integer")
     *
     * @var int
     */
    private $nbOpenings = 0;

    /**
     * Is the evaluation used to compute the workspace evaluation ?
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $required = false;

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getNbAttempts(): int
    {
        return $this->nbAttempts ?? 0;
    }

    public function setNbAttempts(int $nbAttempts)
    {
        $this->nbAttempts = $nbAttempts;
    }

    public function getNbOpenings(): int
    {
        return $this->nbOpenings ?? 0;
    }

    public function setNbOpenings(int $nbOpenings)
    {
        $this->nbOpenings = $nbOpenings;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required)
    {
        $this->required = $required;
    }
}
