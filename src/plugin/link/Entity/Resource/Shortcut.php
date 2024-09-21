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
 */
#[ORM\Table(name: 'claro_resource_shortcut')]
#[ORM\Entity]
class Shortcut extends AbstractResource
{
    /**
     * The targeted resource node.
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: true)]
    #[ORM\ManyToOne(targetEntity: ResourceNode::class)]
    private ?ResourceNode $target = null;

    /**
     * Sets the target of the shortcut.
     */
    public function setTarget(ResourceNode $target): void
    {
        $this->target = $target;
    }

    /**
     * Gets the target of the shortcut.
     */
    public function getTarget(): ?ResourceNode
    {
        return $this->target;
    }
}
