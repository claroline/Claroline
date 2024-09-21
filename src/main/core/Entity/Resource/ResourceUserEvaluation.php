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

use Doctrine\DBAL\Types\Types;
use Claroline\EvaluationBundle\Entity\AbstractUserEvaluation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents the global evaluation of a User for a ResourceNode.
 * There is only one for a user and a resource.
 */
#[ORM\Table(name: 'claro_resource_user_evaluation')]
#[ORM\UniqueConstraint(name: 'resource_user_evaluation', columns: ['resource_node', 'user_id'])]
#[ORM\Entity]
class ResourceUserEvaluation extends AbstractUserEvaluation
{
    /**
     *
     *
     * @var ResourceNode
     */
    #[ORM\JoinColumn(name: 'resource_node', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private $resourceNode;

    /**
     * @var int
     */
    #[ORM\Column(name: 'nb_attempts', type: Types::INTEGER)]
    private $nbAttempts = 0;

    /**
     * @var int
     */
    #[ORM\Column(name: 'nb_openings', type: Types::INTEGER)]
    private $nbOpenings = 0;

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceNode;
    }

    public function setResourceNode(ResourceNode $resourceNode): void
    {
        $this->resourceNode = $resourceNode;
    }

    public function getNbAttempts(): int
    {
        return $this->nbAttempts ?? 0;
    }

    public function setNbAttempts(int $nbAttempts): void
    {
        $this->nbAttempts = $nbAttempts;
    }

    public function getNbOpenings(): int
    {
        return $this->nbOpenings ?? 0;
    }

    public function setNbOpenings(int $nbOpenings): void
    {
        $this->nbOpenings = $nbOpenings;
    }

    public function isRequired(): bool
    {
        if ($this->resourceNode) {
            return $this->resourceNode->isRequired();
        }

        return false;
    }

    public function getEstimatedDuration(): ?int
    {
        if ($this->resourceNode) {
            return $this->resourceNode->getEstimatedDuration();
        }

        return 0;
    }

    public function isEvaluated(): bool
    {
        if ($this->resourceNode) {
            return $this->resourceNode->isEvaluated();
        }

        return false;
    }
}
