<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Entity\UserEvaluation;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\EvaluationBundle\Finder\ResourceAttemptType;
use Claroline\EvaluationBundle\Repository\UserEvaluation\ResourceAttemptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents an attempt of a User to a ResourceNode.
 * There may be several for a user and a resource.
 */
#[ORM\Table(name: 'claro_resource_evaluation')]
#[ORM\Entity(repositoryClass: ResourceAttemptRepository::class)]
#[CrudEntity(finderClass: ResourceAttemptType::class)]
class ResourceAttempt extends AbstractEvaluation
{
    #[ORM\JoinColumn(name: 'resource_user_evaluation', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: ResourceEvaluation::class)]
    private ?ResourceEvaluation $resourceUserEvaluation;

    #[ORM\Column(name: 'evaluation_comment', type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(name: 'more_data', type: Types::JSON, nullable: true)]
    private ?array $data = [];

    public function getResourceNode(): ?ResourceNode
    {
        return $this->resourceUserEvaluation->getResourceNode();
    }

    public function getResourceUserEvaluation(): ?ResourceEvaluation
    {
        return $this->resourceUserEvaluation;
    }

    public function setResourceUserEvaluation(ResourceEvaluation $resourceUserEvaluation): void
    {
        $this->resourceUserEvaluation = $resourceUserEvaluation;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment = null): void
    {
        $this->comment = $comment;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}
