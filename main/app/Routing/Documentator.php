<?php

namespace Claroline\AppBundle\Routing;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\FinderException;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SchemaProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Routing\Route;

class Documentator
{
    /**
     * Crud constructor.
     *
     * @param Router $router
     */
    public function __construct(
        FinderProvider $finder,
        SerializerProvider $serializer,
        SchemaProvider $schema,
        Reader $reader,
        Finder $router
    ) {
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->reader = $reader;
        $this->router = $router;
        $this->schema = $schema;
    }

    public function documentRoute(Route $route)
    {
        $base = [];

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

        foreach ($routes->getIterator() as $route) {
            $method = strtolower(isset($route->getMethods()[0]) ? $route->getMethods()[0] : 'get');
            $documented[$route->getPath()][$method] = $this->documentRoute($route);
            $documented[$route->getPath()][$method]['tags'] = [$class];
        }

        return $documented;
    }

    private function parseDoc(ApiDoc $doc, $objectClass)
    {
        $data = ['parameters' => []];

        $description = $doc->getDescription() ?
          $this->parseDescription($doc->getDescription(), $objectClass) : null;

        $queryString = $doc->getQueryString() ?
            $this->parseQueryString($doc->getQueryString(), $objectClass) : [];

        $parameters = $doc->getParameters() ?
            $this->parseParameters($doc->getParameters(), $objectClass) : [];

        $body = $doc->getBody() ?
            $this->parseBody($doc->getBody(), $objectClass) : null;

        $responses = $doc->getResponse() ?
            $this->parseResponse($doc->getResponse(), $objectClass) : null;

        $data['produce'] = $doc->getProduce() ?? ['application/json'];

        if ($description) {
            $data['description'] = $description;
        }

        if ($responses) {
            $data['responses'] = $responses;
        }

        if ($queryString || $parameters) {
            $data['parameters'] = array_merge($queryString, $parameters);
        }

        if (isset($body['parameters'])) {
            $data['parameters'] = array_merge($data['parameters'], $body['parameters']);
        }

        return $data;
    }

    private function parseDescription($description, $objectClass)
    {
        return str_replace('$class', $objectClass, $description);
    }

    private function parseParameters($parameters, $objectClass)
    {
        $doc = [];

        foreach ($parameters as $parameter) {
            $data['name'] = $parameter['name'];
            $data['type'] = $parameter['type'];
            $data['required'] = true;
            $data['description'] = isset($parameters['description']) ? $parameters['description'] : null;
            $data['in'] = 'path';
            $doc[] = $data;
        }

        return $doc;
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

                try {
                    $finder = $this->finder->get($finderClass);
                    $finderDoc = [];

                    if (method_exists($finder, 'getFilters')) {
                        $filters = $finder->getFilters();
                        $foundFilters = [];

                        foreach ($filters as $name => $data) {
                            $addFilter = true;
                            //check we didn't exclude it here
                            foreach ($queryOptions as $option) {
                                if (null !== strpos($option, '!') && substr($option, 1) === $name) {
                                    $addFilter = false;
                                }
                            }

                            $foundFilters[] = $name;

                            if ($addFilter && '$defaults' !== $name) {
                                $finderDoc[] = [
                                  'name' => "filters[{$name}]",
                                  'type' => $data['type'],
                                  'description' => $data['description'],
                                  'required' => false,
                                  'in' => 'query',
                              ];
                            }
                        }

                        if (array_key_exists('$defaults', $filters) && class_exists($finderClass)) {
                            $refClass = new \ReflectionClass($finderClass);
                            $properties = $refClass->getProperties();

                            foreach ($properties as $refProp) {
                                if (!in_array($refProp->getName(), $foundFilters)) {
                                    $annotation = $this->reader->getPropertyAnnotation($refProp, 'Doctrine\\ORM\\Mapping\\Column');

                                    if ($annotation) {
                                        $finderDoc[] = [
                                            'name' => "filters[{$refProp->getName()}]",
                                            'type' => $annotation->type,
                                            'description' => 'Autogenerated from doctrine annotations (no description found)',
                                            'required' => false,
                                            'in' => 'query',
                                        ];
                                    }
                                }
                            }
                        }

                        $doc = array_merge($finderDoc, $doc);
                    }
                } catch (FinderException $e) {
                    $doc[] = $query;
                    //no finder found; so we use the doctrine default methods & filters
                   //todo: write that doc
                }
            } else {
                $query['in'] = 'query';
                $doc[] = $query;
            }
        }

        return $doc;
    }

    private function parseBody($body, $objectClass)
    {
        $requestBody = [];
        $examples = [];
        $data = [];

        if (is_array($body)) {
            if (isset($body['schema']) && '$schema' === $body['schema']) {
                $data['schema']['$ref'] = '#/extendedModels/'.$objectClass.'/post';
                $data['in'] = 'body';
                $data['name'] = 'body';
                $examples = $this->schema->getSamples($objectClass);
                $data['examples'] = $examples;

                $requestBody[] = $data;
            }
        }

        return ['parameters' => $requestBody, 'samples' => $examples];
    }

    private function parseResponse($responses, $objectClass)
    {
        $data = [];

        foreach ($responses as $response) {
            $options = explode('=', $response);
            $objectClass = isset($options[1]) ? $options[1] : $objectClass;

            if (is_string($response) && null !== strpos($response, '$object')) {
                $options = explode('=', $response);

                $objectClass = isset($options[1]) ? $options[1] : $objectClass;
                $data['200']['description'] = 'successfull operation';
                $data['200']['schema']['$ref'] = '#/definitions/'.$objectClass;
            } elseif (is_string($response) && null !== strpos($response, '$list')) {
                $options = explode('=', $response);
                $objectClass = isset($options[1]) ? $options[1] : $objectClass;
                $data['200']['description'] = 'successfull operation';
                $data['200']['schema']['$ref'] = '#/extendedModels/'.$objectClass.'/list';
            }
        }

        return $data;
    }
}
