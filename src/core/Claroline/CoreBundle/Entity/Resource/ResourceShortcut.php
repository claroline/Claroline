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
     * @ORM\Id
     * @ORM\Column(type="integer")
     **/
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource",
     *     inversedBy="shortcuts"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $resource;

    public function setResource(AbstractResource $resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }
}
