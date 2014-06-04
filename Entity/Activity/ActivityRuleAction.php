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
 * @ORM\Entity
 * @ORM\Table(name="claro_activity_rule_action")
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

    /**
     * @ORM\Column(name="rule_type", nullable=true)
     */
    protected $type;
}
