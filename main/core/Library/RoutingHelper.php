<?php

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RoutingHelper
{
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function indexUrl()
    {
        return $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function indexPath()
    {
        return $this->router->generate('claro_index');
    }

    public function desktopUrl($toolName = null)
    {
        return $this->indexUrl().'#/desktop/'.$toolName;
    }

    public function desktopPath($toolName = null)
    {
        return $this->indexPath().'#/desktop/'.$toolName;
    }

    public function resourceUrl($resource)
    {
        return $this->indexUrl().'#'.$this->resourceFragment($resource);
    }

    public function resourcePath($resource)
    {
        return $this->indexPath().'#'.$this->resourceFragment($resource);
    }

    public function resourceFragment($resource)
    {
        $slug = null;
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
            return $this->workspaceFragment($wsSlug, 'resources').'/'.$slug;
        } else {
            return '/desktop/resources/'.$slug;
        }
    }

    public function workspaceUrl($workspace, $toolName = null)
    {
        return $this->indexUrl().'#'.$this->workspaceFragment($workspace, $toolName);
    }

    public function workspacePath($workspace, $toolName = null)
    {
        return $this->indexPath().'#'.$this->workspaceFragment($workspace, $toolName);
    }

    public function workspaceFragment($workspace, $toolName = null)
    {
        $slug = null;
        if ($workspace instanceof Workspace) {
            $slug = $workspace->getSlug();
        } elseif (is_array($workspace) && isset($workspace['slug'])) {
            $slug = $workspace['slug'];
        } elseif (is_string($workspace)) {
            $slug = $workspace;
        }

        $fragment = '/desktop/workspaces/open/'.$slug;
        if ($toolName) {
            $fragment .= '/'.$toolName;
        }

        return $fragment;
    }
}
