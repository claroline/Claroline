<?php

namespace Claroline\AppBundle\Routing;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Doctrine\Common\Annotations\Reader;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Route;

/**
 * @DI\Service("claroline.api.routing.documentator")
 */
class Documentator
{
    /**
     * Crud constructor.
     *
     * @DI\InjectParams({
     *     "finder"     = @DI\Inject("claroline.api.finder"),
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "router"     = @DI\Inject("claroline.api.routing.finder"),
     *     "reader"    = @DI\Inject("annotation_reader")
     * })
     *
     * @param Router $router
     */
    public function __construct(
        FinderProvider $finder,
        SerializerProvider $serializer,
        Reader $reader,
        Finder $router
    ) {
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->reader = $reader;
        $this->router = $router;
    }

    public function documentRoute(Route $route)
    {
        $base = [
            'url' => $route->getPath(),
            'method' => $route->getMethods(),
        ];

        $defaults = $route->getDefaults();

        if (isset($defaults['_controller'])) {
            $parts = explode(':', $defaults['_controller']);
            $class = $parts[0];
            $method = $parts[2];
            $extended = [];

            if (class_exists($class)) {
                $refClass = new \ReflectionClass($class);

                if (method_exists($class, $method)) {
                    $controller = $refClass->newInstanceWithoutConstructor();
                    $objectClass = $controller->getClass();
                    //doesn't work with the @API because it's deprecated
                    if ($objectClass) {
                        $refMethod = new \ReflectionMethod($class, $method);
                        $doc = $this->reader->getMethodAnnotation($refMethod, 'Claroline\\AppBundle\\Annotations\\ApiDoc');

                        if ($doc) {
                            $extended = $this->parseDoc($doc, $objectClass);
                        }
                    }
                }
            }
        }

        return array_merge($base, $extended);
    }

    public function documentClass($class)
    {
        $routes = $this->router->find($class);
        $documented = [];

        foreach ($routes->getIterator() as $name => $route) {
            $documented[$name] = $this->documentRoute($route);
        }

        return $documented;
    }

    private function parseDoc(ApiDoc $doc, $objectClass)
    {
        $data = [];

        $description = $doc->getDescription() ?
          $this->parseDescription($doc->getDescription(), $objectClass) : null;

        $queryString = $doc->getQueryString() ?
            $this->parseQueryString($doc->getQueryString(), $objectClass) : null;

        $body = $doc->getBody() ?
            $this->parseBody($doc->getBody(), $objectClass) : null;

        if ($description) {
            $data['description'] = $description;
        }

        if ($queryString) {
            $data['queryString'] = $queryString;
        }

        if ($doc->getParameters()) {
            $data['parameters'] = $doc->getParameters();
        }

        if ($body) {
            $data['body'] = $body;
        }

        return $data;
    }

    private function parseDescription($description, $objectClass)
    {
        return str_replace('$class', $objectClass, $description);
    }

    private function parseQueryString($queryStrings, $objectClass)
    {
        $doc = [];

        foreach ($queryStrings as $query) {
            if (is_string($query) && null !== strpos($query, '$finder')) {
                $queryOptions = explode('&', $query);
                $finderOptions = array_shift($queryOptions);
                $finderOptions = explode('=', $finderOptions);
                $finderClass = isset($finderOptions[1]) ? $finderOptions[1] : $objectClass;
                $finder = $this->finder->get($finderClass);
                $finderDoc = [];

                if (method_exists($finder, 'getFilters')) {
                    $filters = $finder->getFilters();

                    foreach ($filters as $name => $data) {
                        $addFilter = true;
                        //check we didn't exclude it here
                        foreach ($queryOptions as $option) {
                            if (null !== strpos($option, '!') && substr($option, 1) === $name) {
                                $addFilter = false;
                            }
                        }

                        if ($addFilter) {
                            $finderDoc[] = [
                                'name' => "filter[{$name}]",
                                'type' => $data['type'],
                                'description' => $data['description'],
                            ];
                        }
                    }

                    $doc = array_merge($finderDoc, $doc);
                }
            } else {
                $doc[] = $query;
            }
        }

        return $doc;
    }

    private function parseBody($body, $objectClass)
    {
        if (is_array($body)) {
            if (isset($body['schema']) && '$schema' === $body['schema']) {
                //cast to array recursively
                $body['schema'] = json_decode(json_encode($this->serializer->getSchema($objectClass)), true);
            }
        }

        return $body;
    }
}
