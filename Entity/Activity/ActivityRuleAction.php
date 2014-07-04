<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Activity\ActivityRuleActionRepository")
 * @ORM\Table(
 *     name="claro_activity_rule_action",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="activity_rule_unique_action_resource_type", columns={"log_action", "resource_type_id"})
 *     }
 * )
 */
class ActivityRuleAction
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="CASCADE", nullable=true)
     */
    protected $resourceType;

    /**
     * @ORM\Column(name="log_action", nullable=false)
     */
    protected $action;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getResourceType()
    {
        return $this->resourceType;
    }

    public function setResourceType($resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }
}
