<?php

namespace Claroline\AppBundle\Routing;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Service("claroline.api.routing.finder")
 */
class Finder
{
    /**
     * Crud constructor.
     *
     * @DI\InjectParams({
     *     "router" = @DI\Inject("router"),
     * })
     *
     * @param Router $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function find($class)
    {
        /** @var RouteCollection */
        $collection = $this->router->getRouteCollection();
        $describeCollection = new RouteCollection();

        foreach ($collection->getIterator() as $key => $route) {
            if ($route instanceof ApiRoute && $class === $route->getClass()) {
                $describeCollection->add($key, $route);
            } else {
                $defaults = $route->getDefaults();
                if (isset($defaults['_controller'])) {
                    $controllerClass = explode(':', $defaults['_controller'])[0];
                    if (class_exists($controllerClass)) {
                        $refClass = new \ReflectionClass($controllerClass);

                        if ($refClass->isSubClassOf('Claroline\AppBundle\Controller\AbstractCrudController')) {
                            $controller = $refClass->newInstanceWithoutConstructor();
                            if ($class === $controller->getClass()) {
                                $describeCollection->add($key, $route);
                            }
                        }
                    }
                }
            }
        }

        return $describeCollection;
    }

    public function getHandledClasses()
    {
        $classes = [];

        /** @var RouteCollection */
        $collection = $this->router->getRouteCollection();

        foreach ($collection->getIterator() as $route) {
            if ($route instanceof ApiRoute && !in_array($route->getClass(), $classes)) {
                $classes[] = $route->getClass();
            } else {
                $defaults = $route->getDefaults();
                if (isset($defaults['_controller'])) {
                    $controllerClass = explode(':', $defaults['_controller'])[0];
                    if (class_exists($controllerClass)) {
                        $refClass = new \ReflectionClass($controllerClass);

                        if ($refClass->isSubClassOf('Claroline\AppBundle\Controller\AbstractCrudController')) {
                            $controller = $refClass->newInstanceWithoutConstructor();
                            if (!in_array($controller->getClass(), $classes)) {
                                $classes[] = $controller->getClass();
                            }
                        }
                    }
                }
            }
        }

        return array_filter($classes);
    }
}
