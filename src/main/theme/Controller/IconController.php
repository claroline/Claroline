<?php

namespace Claroline\ThemeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

#[Route(path: '/icon')]
class IconController
{
    public function __construct(
        private readonly string $projectDir
    ) {
    }

    #[Route(path: '/system', name: 'apiv2_icon_system_list', methods: ['GET'])]
    public function listSystemAction(): JsonResponse
    {
        $metadataPath = $this->projectDir.'/node_modules/@fortawesome/fontawesome-free/metadata/categories.yml';

        $icons = Yaml::parseFile($metadataPath);

        return new JsonResponse($icons);
    }
}
