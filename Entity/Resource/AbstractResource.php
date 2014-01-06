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

/** @ORM\MappedSuperclass */
abstract class AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $resourceNode;

    protected $name = '';

    //to remove ?
    protected $mimeType;

    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;
    }

    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    public function getId()
    {
        return $this->id;
    }

    //for forms (otherwise name won't exist)
    //@todo this should be removed
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }
}
