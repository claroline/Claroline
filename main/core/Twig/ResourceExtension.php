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
    private $resourceNodeManager;

    /**
     * ResourceExtension constructor.
     *
     * @DI\InjectParams({
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node")
     * })
     *
     * @param ResourceNodeManager $resourceNodeManager
     */
    public function __construct(ResourceNodeManager $resourceNodeManager)
    {
        $this->resourceNodeManager = $resourceNodeManager;
    }

    public function getName()
    {
        return 'resource_extension';
    }

    public function getFunctions()
    {
        return [
            'isCodeProtected' => new \Twig_SimpleFunction('isCodeProtected', [$this, 'isCodeProtected']),
            'requiresUnlock' => new \Twig_SimpleFunction('requiresUnlock', [$this, 'requiresUnlock']),
        ];
    }

    public function isCodeProtected(ResourceNode $resourceNode)
    {
        return $this->resourceNodeManager->isCodeProtected($resourceNode);
    }

    public function requiresUnlock(ResourceNode $resourceNode)
    {
        return $this->resourceNodeManager->requiresUnlock($resourceNode);
    }
}
