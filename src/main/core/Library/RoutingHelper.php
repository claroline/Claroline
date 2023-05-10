<?php

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RoutingHelper
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function indexUrl(): string
    {
        return $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function indexPath(): string
    {
        return $this->router->generate('claro_index');
    }

    public function desktopUrl(string $toolName = null): string
    {
        return $this->indexUrl().'#/desktop/'.$toolName;
    }

    public function desktopPath(string $toolName = null): string
    {
        return $this->indexPath().'#/desktop/'.$toolName;
    }

    public function resourceUrl(ResourceNode|array|string $resource): string
    {
        return $this->indexUrl().'#'.$this->resourceFragment($resource);
    }

    public function resourcePath(ResourceNode|array|string $resource): string
    {
        return $this->indexPath().'#'.$this->resourceFragment($resource);
    }

    public function adminUrl(string $adminToolName = null): string
    {
        return $this->indexUrl().'#/admin/'.$adminToolName;
    }

    public function adminPath(string $adminToolName = null): string
    {
        return $this->indexPath().'#/admin/'.$adminToolName;
    }

    public function resourceFragment(ResourceNode|array|string $resource): string
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

    public function workspaceUrl(Workspace|array|string $workspace, string $toolName = null): string
    {
        return $this->indexUrl().'#'.$this->workspaceFragment($workspace, $toolName);
    }

    public function workspacePath(Workspace|array|string $workspace, string $toolName = null): string
    {
        return $this->indexPath().'#'.$this->workspaceFragment($workspace, $toolName);
    }

    public function workspaceFragment(Workspace|array|string $workspace, string $toolName = null): string
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
