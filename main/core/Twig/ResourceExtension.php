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

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

/**
 * @Service
 * @Tag("twig.extension")
 */
class ResourceExtension extends \Twig_Extension
{
    protected $resourceManager;

    /**
     * @InjectParams({
     *     "resourceManager" = @Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    public function getFunctions()
    {
        return array(
            'isMenuActionImplemented' => new \Twig_Function_Method($this, 'isMenuActionImplemented'),
            'getCurrentUrl' => new \Twig_Function_Method($this, 'getCurrentUrl'),
        );
    }

    public function getName()
    {
        return 'resource_extension';
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
