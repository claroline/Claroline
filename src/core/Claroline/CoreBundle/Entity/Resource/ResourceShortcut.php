<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceShortcutRepository")
 * @ORM\Table(name="claro_resource_shortcut")
 */
class ResourceShortcut extends AbstractResource
{
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     inversedBy="shortcuts"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $target;

    public function setTarget(ResourceNode $target)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }
}
