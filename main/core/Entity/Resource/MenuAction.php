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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_menu_action")
 */
class MenuAction
{
    use Id;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $decoder;

    /**
     * @ORM\Column(name="group_name", nullable=true)
     *
     * @var string
     */
    private $group;

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $scope = [];

    /**
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $api = [];

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType",
     *     inversedBy="actions",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="resource_type_id", onDelete="SET NULL")
     */
    private $resourceType;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ResourceType
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @param ResourceType $resourceType
     */
    public function setResourceType(ResourceType $resourceType = null)
    {
        $this->resourceType = $resourceType;
    }

    /**
     * @param string $decoder
     */
    public function setDecoder($decoder)
    {
        $this->decoder = $decoder;
    }

    /**
     * @return string
     */
    public function getDecoder()
    {
        return $this->decoder;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return array
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param array $scope
     */
    public function setScope(array $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return array
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param array $api
     */
    public function setApi(array $api)
    {
        $this->api = $api;
    }
}
