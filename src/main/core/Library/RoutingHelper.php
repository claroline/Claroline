<?php

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class RoutingHelper
{
    public function __construct(
        private readonly RouterInterface $router
    ) {
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

    public function resourceUrl(ResourceNode $resource): string
    {
        return $this->indexUrl().'#'.$this->resourceFragment($resource);
    }

    public function resourcePath(ResourceNode $resource): string
    {
        return $this->indexPath().'#'.$this->resourceFragment($resource);
    }

    public function adminUrl(string $adminToolName = null): string
    {
        return $this->indexUrl().'#/administration/'.$adminToolName;
    }

    public function adminPath(string $adminToolName = null): string
    {
        return $this->indexPath().'#/administration/'.$adminToolName;
    }

    /**
     * @internal should be simplified
     */
    public function resourceFragment(ResourceNode $resource): string
    {
        return $this->workspaceFragment($resource->getWorkspace(), 'resources').'/'.$resource->getSlug();
    }

    public function workspaceUrl(Workspace $workspace, string $toolName = null): string
    {
        return $this->indexUrl().'#'.$this->workspaceFragment($workspace, $toolName);
    }

    public function workspacePath(Workspace $workspace, string $toolName = null): string
    {
        return $this->indexPath().'#'.$this->workspaceFragment($workspace, $toolName);
    }

    private function workspaceFragment(Workspace $workspace, string $toolName = null): string
    {
        $fragment = '/desktop/workspaces/open/'.$workspace->getSlug();
        if ($toolName) {
            $fragment .= '/'.$toolName;
        }

        return $fragment;
    }
}
