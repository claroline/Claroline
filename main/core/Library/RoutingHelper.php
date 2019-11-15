<?php

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Routing\RouterInterface;

class RoutingHelper
{
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function resourcePath(ResourceNode $resource)
    {
        return $this->router->generate('claro_index')
          .'#'.$this->resourceFragment($resource);
    }

    public function resourceFragment($resource)
    {
        $wsSlug = null;

        if ($resource instanceof ResourceNode) {
            $slug = $resource->getSlug();
            $wsSlug = $resource->getWorkspace()->getSlug();
        } elseif (is_array($resource)) {
            if (isset($resource['slug'])) {
                $slug = $resource['slug'];
            } else {
                $slug = $resource['guid'];
            }

            if (isset($resource['workspace']) && isset($resource['workspace']['slug'])) {
                $wsSlug = $resource['workspace']['slug'];
            }
        } elseif (is_string($resource)) {
            $slug = $resource;
        }

        if ($wsSlug) {
            return '/desktop/open/workspaces/'.$wsSlug.'/resources/'.$slug;
        } else {
            return '/desktop/resources/'.$slug;
        }
    }

    public function workspacePath(Workspace $workspace)
    {
    }
}
