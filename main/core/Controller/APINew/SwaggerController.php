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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SchemaProvider;
use Claroline\CoreBundle\API\Serializer\Platform\ClientSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/swagger")
 */
class SwaggerController
{
    /**
     * ParametersController constructor.
     */
    public function __construct($routerFinder, $documentator, SchemaProvider $schemaProvider, ClientSerializer $configuration, $rootDir)
    {
        $this->routerFinder = $routerFinder;
        $this->documentator = $documentator;
        $this->schemaProvider = $schemaProvider;
        $this->configuration = $configuration;
        $this->rootDir = $rootDir.'/..';
        $this->baseUri = 'https://github.com/claroline/Distribution/tree/master';
    }

    /**
     * @Route("", name="apiv2_swagger_get")
     * @Method("GET")
     */
    public function getApiAction()
    {
        $config = $this->configuration->serialize();

        $swagger = [
          'swagger' => '2.0',
          'info' => [
              'version' => 'v2',
              'title' => 'Claroline API',
              'description' => 'Claroline API',
              'termsOfService' => 'None',
              'contact' => [
                  'name' => 'Claroline',
                  'url' => 'www.claroline.net',
                  'email' => 'claroline@ovh.com',
              ],
              'license' => [
                  'name' => 'GPL-3.0-or-later',
                  'url' => 'https://www.gnu.org/licenses/gpl-3.0.fr.html',
              ],
          ],
          'basePath' => $config['swagger']['base'],
        ];

        $classes = $this->routerFinder->getHandledClasses();

        $data = new \StdClass();

        foreach ($classes as $class) {
            $data = (object) array_merge((array) $data, $this->documentator->documentClass($class));
        }

        $definitions = [];

        foreach ($classes as $class) {
            $def = json_decode(json_encode($this->schemaProvider->getSchema($class, [Options::IGNORE_COLLECTIONS])), true);
            //we need to mode, return and submit
            $defFull = json_decode(json_encode($this->schemaProvider->getSchema($class)), true);

            if (is_array($defFull)) {
                $definitions[$class] = $defFull;
            }

            if (is_array($def)) {
                $absolutePath = $this->rootDir.'/vendor/claroline/distribution/main/core/Resources/schemas/datalist/list.json';
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
