<?php

namespace Claroline\ThemeBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * @Route("/icon")
 */
class IconController
{
    /** @var string */
    private $kernelDir;

    /**
     * IconController constructor.
     *
     * @param string $kernelDir
     */
    public function __construct($kernelDir)
    {
        $this->kernelDir = $kernelDir;
    }

    /**
     * @Route("/system", name="apiv2_icon_system_list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function listSystemAction()
    {
        $metadataPath = $this->kernelDir.'/../node_modules/@fortawesome/fontawesome-free/metadata/categories.yml';

        $icons = Yaml::parseFile($metadataPath);

        return new JsonResponse($icons);
    }
}
