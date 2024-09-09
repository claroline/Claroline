<?php

namespace Claroline\AppBundle\Routing;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class Finder
{
    public function __construct(
        private readonly RouterInterface $router
    ) {
    }

    public function find(string $class): RouteCollection
    {
        $collection = $this->router->getRouteCollection();
        $describeCollection = new RouteCollection();

        foreach ($collection->getIterator() as $key => $route) {
            $defaults = $route->getDefaults();
            if (isset($defaults['_controller'])) {
                $controllerClass = explode(':', $defaults['_controller'])[0];
                if (class_exists($controllerClass)) {
                    $refClass = new \ReflectionClass($controllerClass);

                    if ($refClass->isSubClassOf(AbstractCrudController::class)) {
                        if ($class === $controllerClass::getClass()) {
                            $describeCollection->add($key, $route);
                        }
                    }
                }
            }
        }

        return $describeCollection;
    }

    public function getHandledClasses(): array
    {
        $classes = [];

        $collection = $this->router->getRouteCollection();

        foreach ($collection->getIterator() as $route) {
            $defaults = $route->getDefaults();
            if (isset($defaults['_controller'])) {
                $controllerClass = explode(':', $defaults['_controller'])[0];
                if (class_exists($controllerClass)) {
                    $refClass = new \ReflectionClass($controllerClass);

                    if ($refClass->isSubClassOf(AbstractCrudController::class)) {
                        if (!in_array($controllerClass::getClass(), $classes)) {
                            $classes[] = $controllerClass::getClass();
                        }
                    }
                }
            }
        }

        return array_values($classes);
    }
}
