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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractResource
{
    use Id;
    use Uuid;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var ResourceNode
     */
    protected $resourceNode;

    /**
     * Only used for setting ResourceNode mimeType in old creation.
     *
     * @var string
     *
     * @deprecated
     */
    protected $mimeType;

    /**
     * Only used for setting ResourceNode name in old creation.
     *
     * @var string
     *
     * @deprecated
     */
    protected $name;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Shortcut to access name from Resource.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name ?? $this->getResourceNode()->getName();
    }

    /**
     * @param $name
     *
     * @deprecated Only used by old creation process
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * DO NOT USE IT. It may be empty.
     *
     * @return string
     *
     * @deprecated Only used by old creation process
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     *
     * @deprecated Only used by old creation process
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }
}
