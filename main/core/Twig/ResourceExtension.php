<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class ResourceExtension extends \Twig_Extension
{
    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * @var ResourceNodeManager
     */
    private $resourceNodeManager;

    /**
     * ResourceExtension constructor.
     *
     * @DI\InjectParams({
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node")
     * })
     *
     * @param ResourceManager     $resourceManager
     * @param ResourceNodeManager $resourceNodeManager
     */
    public function __construct(
        ResourceManager $resourceManager,
        ResourceNodeManager $resourceNodeManager)
    {
        $this->resourceManager = $resourceManager;
        $this->resourceNodeManager = $resourceNodeManager;
    }

    public function getName()
    {
        return 'resource_extension';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('get_serialized_node', [$this, 'serializeNode']),
        ];
    }

    public function getFunctions()
    {
        return [
            'isMenuActionImplemented' => new \Twig_Function_Method($this, 'isMenuActionImplemented'),
            'getCurrentUrl' => new \Twig_Function_Method($this, 'getCurrentUrl'),
        ];
    }

    /**
     * Gets a serialized representation of the node of a resource.
     *
     * @param AbstractResource $resource
     *
     * @return array
     */
    public function serializeNode(AbstractResource $resource)
    {
        return $this->resourceNodeManager->serialize($resource->getResourceNode());
    }

    public function isMenuActionImplemented(ResourceType $resourceType = null, $menuName)
    {
        return $this->resourceManager->isResourceActionImplemented($resourceType, $menuName);
    }

    public function getCurrentUrl()
    {
        return '';
    }
}
