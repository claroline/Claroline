<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LinkBundle\Entity\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;

/**
 * A shortcut to access another resource in the platform.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_resource_shortcut")
 */
class Shortcut extends AbstractResource
{
    /**
     * The targeted resource node.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var ResourceNode
     */
    private $target;

    /**
     * Sets the target of the shortcut.
     *
     * @param ResourceNode $target
     */
    public function setTarget(ResourceNode $target)
    {
        $this->target = $target;
    }

    /**
     * Gets the target of the shortcut.
     *
     * @return ResourceNode
     */
    public function getTarget()
    {
        return $this->target;
    }
}
