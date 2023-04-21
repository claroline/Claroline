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

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents an attempt of a User to a ResourceNode.
 * There may be several for a user and a resource.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository")
 * @ORM\Table(name="claro_resource_evaluation")
 */
class ResourceEvaluation extends AbstractEvaluation
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation"
     * )
     * @ORM\JoinColumn(name="resource_user_evaluation", onDelete="CASCADE")
     *
     * @var ResourceUserEvaluation
     */
    private $resourceUserEvaluation;

    /**
     * @ORM\Column(type="text", name="evaluation_comment", nullable=true)
     *
     * @var string
     */
    private $comment;

    /**
     * @ORM\Column(name="more_data", type="json", nullable=true)
     *
     * @var array
     */
    private $data;

    public function getResourceUserEvaluation(): ?ResourceUserEvaluation
    {
        return $this->resourceUserEvaluation;
    }

    public function setResourceUserEvaluation(ResourceUserEvaluation $resourceUserEvaluation): void
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
