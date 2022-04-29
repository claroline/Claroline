<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\SchemaProvider;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Routing\Documentator;
use Claroline\AppBundle\Routing\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/swagger")
 */
class SwaggerController
{
    /** @var Finder */
    private $routerFinder;
    /** @var Documentator */
    private $documentator;
    /** @var SchemaProvider */
    private $schemaProvider;
    /** @var PlatformManager */
    private $platformManager;
    /** @var string */
    private $rootDir;

    public function __construct(
        Finder $routerFinder,
        Documentator $documentator,
        SchemaProvider $schemaProvider,
        PlatformManager $platformManager,
        string $rootDir
    ) {
        $this->routerFinder = $routerFinder;
        $this->documentator = $documentator;
        $this->schemaProvider = $schemaProvider;
        $this->platformManager = $platformManager;
        $this->rootDir = $rootDir;
    }

    /**
     * @Route("", name="apiv2_swagger_get", methods={"GET"})
     */
    public function getApiAction()
    {
        $swagger = [
            'swagger' => '2.0',
            'info' => [
                'version' => '2.0',
                'title' => 'Claroline API',
                'termsOfService' => null,
                'license' => [
                    'name' => 'GPL-3.0-or-later',
                    'url' => 'https://www.gnu.org/licenses/gpl-3.0.fr.html',
                ],
            ],
            'basePath' => $this->platformManager->getUrl(),
        ];

        $classes = $this->routerFinder->getHandledClasses();

        $data = new \StdClass();

        foreach ($classes as $class) {
            $data = (object) array_merge((array) $data, $this->documentator->documentClass($class));
        }

        $definitions = [];

        foreach ($classes as $class) {
            $def = json_decode(json_encode($this->schemaProvider->getSchema($class, [SchemaProvider::IGNORE_COLLECTIONS])), true);
            //we need to mode, return and submit
            $defFull = json_decode(json_encode($this->schemaProvider->getSchema($class)), true);

            if (is_array($defFull)) {
                $definitions[$class] = $defFull;
            }

            if (is_array($def)) {
                $absolutePath = $this->rootDir.'/src/main/core/Resources/schemas/datalist/list.json';
                $listSchema = $this->schemaProvider->loadSchema($absolutePath);
                $listSchema = json_decode(json_encode($listSchema), true);
                $listSchema['properties']['data'] = [
                    'type' => 'array',
                    'description' => 'the object list',
                    'uniqueItems' => true,
                    'items' => [
                        '$ref' => '#/definitions/'.$class,
                    ],
                ];

                $swagger['extendedModels'][$class]['list'] = $listSchema;
                $swagger['extendedModels'][$class]['post'] = $def;
            }
        }

        $swagger['paths'] = $data;
        $swagger['definitions'] = $definitions;

        return new JsonResponse($swagger);
    }
}
