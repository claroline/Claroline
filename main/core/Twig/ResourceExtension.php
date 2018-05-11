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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
    private $resourceNodeManager;

    /**
     * ResourceExtension constructor.
     *
     * @DI\InjectParams({
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node")
     * })
     *
     * @param ResourceManager $resourceManager
     */
    public function __construct(ResourceNodeManager $resourceNodeManager, ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
        $this->resourceNodeManager = $resourceNodeManager;
    }

    public function getName()
    {
        return 'resource_extension';
    }

    public function getFunctions()
    {
        return [
            'isMenuActionImplemented' => new \Twig_SimpleFunction('isMenuActionImplemented', [$this, 'isMenuActionImplemented']),
            'getCurrentUrl' => new \Twig_SimpleFunction('getCurrentUrl', [$this, 'getCurrentUrl']),
            'isCodeProtected' => new \Twig_SimpleFunction('isCodeProtected', [$this, 'isCodeProtected']),
            'requiresUnlock' => new \Twig_SimpleFunction('requiresUnlock', [$this, 'requiresUnlock']),
        ];
    }

    public function isMenuActionImplemented(ResourceType $resourceType = null, $menuName)
    {
        return $this->resourceManager->isResourceActionImplemented($resourceType, $menuName);
    }

    public function isCodeProtected(ResourceNode $resourceNode)
    {
        return $this->resourceNodeManager->isCodeProtected($resourceNode);
    }

    public function requiresUnlock(ResourceNode $resourceNode)
    {
        return $this->resourceNodeManager->requiresUnlock($resourceNode);
    }

    public function getCurrentUrl()
    {
        return '';
    }
}
