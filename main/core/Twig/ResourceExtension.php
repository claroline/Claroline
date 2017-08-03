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

use Claroline\CoreBundle\Entity\Resource\ResourceType;
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
     * ResourceExtension constructor.
     *
     * @DI\InjectParams({
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param ResourceManager $resourceManager
     */
    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    public function getName()
    {
        return 'resource_extension';
    }

    public function getFunctions()
    {
        return [
            'isMenuActionImplemented' => new \Twig_Function_Method($this, 'isMenuActionImplemented'),
            'getCurrentUrl' => new \Twig_Function_Method($this, 'getCurrentUrl'),
        ];
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
