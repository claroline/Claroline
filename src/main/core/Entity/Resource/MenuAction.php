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

use Claroline\CoreBundle\Repository\Resource\ResourceActionRepository;
use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_menu_action')]
#[ORM\Entity(repositoryClass: ResourceActionRepository::class)]
class MenuAction
{
    use Id;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING)]
    private $decoder;

    /**
     * @var string
     */
    #[ORM\Column(name: 'group_name', nullable: true)]
    private $group;

    /**
     * @var array
     */
    #[ORM\Column(type: Types::JSON)]
    private $scope = [];

    /**
     * @var array
     */
    #[ORM\Column(type: Types::JSON)]
    private $api = [];

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_default', type: Types::BOOLEAN)]
    private $default = false;

    #[ORM\JoinColumn(name: 'resource_type_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ResourceType::class)]
    private ?ResourceType $resourceType = null;

    /**
     * The plugin which have introduced the action.
     *
     *
     * @var Plugin
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Plugin::class)]
    private ?Plugin $plugin = null;

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

    public function setApi(array $api)
    {
        $this->api = $api;
    }

    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }
}
