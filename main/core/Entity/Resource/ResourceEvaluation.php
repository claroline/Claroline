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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_evaluation")
 */
class ResourceEvaluation extends AbstractResourceEvaluation
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation")
     * @ORM\JoinColumn(name="resource_user_evaluation", onDelete="CASCADE")
     */
    protected $resourceUserEvaluation;

    /**
     * @ORM\Column(type="text", name="evaluation_comment", nullable=true)
     */
    protected $comment;

    /**
     * @ORM\Column(name="more_data", type="json_array", nullable=true)
     */
    protected $data;

    public function getResourceUserEvaluation()
    {
        return $this->resourceUserEvaluation;
    }

    public function setResourceUserEvaluation(ResourceUserEvaluation $resourceUserEvaluation)
    {
        $this->resourceUserEvaluation = $resourceUserEvaluation;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}
