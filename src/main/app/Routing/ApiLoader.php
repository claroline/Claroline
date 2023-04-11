<?php

// src/AppBundle/Routing/ExtraLoader.php

namespace Claroline\AppBundle\Routing;

use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouteCollection;

class ApiLoader extends Loader
{
    // Route format : [path, method, defaults]
    const DEFAULT_MAP = [
        'create' => ['', 'POST'],
        'deleteBulk' => ['', 'DELETE'],
        'list' => ['', 'GET'],
        'find' => ['/find', 'GET'],
        'copyBulk' => ['/copy', 'GET'],
        'update' => ['/{id}', 'PUT'],
        'get' => ['/{field}/{id}', 'GET', ['field' => 'id']],
        'exist' => ['/exist/{field}/{value}', 'GET'],
    ];

    /** @var bool */
    private $loaded = false;
    /** @var FileLocatorInterface */
    private $locator;
    /** @var Reader */
    private $reader;

    public function __construct(
        FileLocatorInterface $locator,
        Reader $reader
    ) {
        $this->locator = $locator;
        $this->reader = $reader;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "api" loader twice');
        }

        $path = $this->locator->locate($resource);
        //this is the default
        $imported = $this->import($resource, 'annotation');

        $routes = new RouteCollection();
        $routes->addCollection($imported);

        $autoRoutes = new RouteCollection();
        $this->loadFromPath($path, $autoRoutes);

        foreach ($routes as $keyRoute => $route) {
            foreach ($autoRoutes as $autoRoute) {
                if ($route->getPath() === $autoRoute->getPath()) {
                    $routes->remove($keyRoute);
                }
            }
        }

        $routes->addCollection($autoRoutes);

        return $routes;
    }

    private function loadFromPath($path, RouteCollection $routes)
    {
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            $file = $fileInfo->getPathname();
            if ($fileInfo->isDir()) {
                $this->loadFromPath($file, $routes);
            } else {
                //find prefix from annotations
                $controller = $this->findClass($file);

                //ok so this is a controller
                if ($controller) {
                    $refClass = new \ReflectionClass($controller);
                    $class = null;
                    $found = false;
                    $prefix = '';
                    $routeNamePrefix = '';
                    $ignore = [];

                    foreach ($this->reader->getClassAnnotations($refClass) as $annotation) {
                        //The route prefix is defined with the sf2 annotations
                        if ($annotation instanceof Route) {
                            $prefix = $annotation->getPath();

                            if (0 === strpos($prefix, '/')) {
                                $prefix = substr($prefix, 1);
                            }

                            $routeNamePrefix = $annotation->getName();
                            if (empty($routeNamePrefix)) {
                                $routeNamePrefix = $prefix;
                            }
                        }
                    }

                    //Find via getClass method of AbstractCrudController

                    if (!$found && $refClass->isSubClassOf('Claroline\AppBundle\Controller\AbstractCrudController')) {
                        $instance = $refClass->newInstanceWithoutConstructor();
                        $class = $instance->getClass();
                        if ($class) {
                            $found = true;
                            $ignore = $instance->getIgnore();

                            foreach ($this->reader->getClassAnnotations($refClass) as $annotation) {
                                //The route prefix is defined with the sf2 annotations
                                if ($annotation instanceof Route) {
                                    $prefix = $annotation->getPath();

                                    if (0 === strpos($prefix, '/')) {
                                        $prefix = substr($prefix, 1);
                                    }
                                }
                            }
                        }
                    }

                    if ($found) {
                        //makeRouteMap is an array of generic routes we want to use
                        foreach ($this->makeRouteMap($controller, $routes, $prefix, $ignore) as $name => $options) {
                            $pattern = '';

                            if (!empty($options[0])) {
                                $pattern = $options[0];
                            }

                            if ($prefix) {
                                $pattern = $prefix.$pattern;
                            }

                            $routeDefaults = [
                                '_controller' => $controller.'::'.$name.'Action',
                                'class' => $class,
                            ];

                            $route = new ApiRoute($pattern, $routeDefaults, []);
                            $route->setAction($name);
                            $route->setMethods([$options[1]]);
                            if (isset($options[2])) {
                                $route->addDefaults($options[2]);
                            }
                            $requirements = $refClass->newInstanceWithoutConstructor()->getRequirements();

                            if (isset($requirements[$name])) {
                                $route->setRequirements($requirements[$name]);
                            }

                            // add the new route to the route collection:
                            $routeName = 'apiv2_'.$routeNamePrefix.'_'.$this->toUnderscore($name);
                            $routes->add($routeName, $route);
                        }
                    }
                }
            }
        }
    }

    private function makeRouteMap($controller, RouteCollection $routes, $prefix, array $ignore)
    {
        $defaults = [];
        $traits = class_uses($controller);

        foreach ($traits as $trait) {
            $refClass = new \ReflectionClass($trait);
            $methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $actionName = preg_replace('/Action/', '', $method->getName());
                $defaults[$actionName][1] = 'GET';

                foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                    if ($annotation instanceof Route) {
                        $defaults[$actionName][0] = $annotation->getPath();
                        $defaults[$actionName][1] = $annotation->getMethods()[0];
                    }
                }
            }
        }

        $mapping = array_merge($defaults, self::DEFAULT_MAP);

        foreach ($ignore as $ignored) {
            unset($mapping[$ignored]);
        }

        return $mapping;
    }

    //@see http://stackoverflow.com/questions/1589468/convert-camelcase-to-under-score-case-in-php-autoload
    public function toUnderscore($string)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'api' === $type;
    }

    /**
     * Returns the full class name for the first class in the file.
     * From the loader classes of sf2 itself.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        if (1 === count($tokens) && T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }
            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }
            if (true === $namespace && defined('T_NAME_QUALIFIED') && \T_NAME_QUALIFIED === $token[0]) {
                $namespace = $token[1];
            } elseif (true === $namespace && T_STRING === $token[0]) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1]) && in_array($tokens[$i][0], [T_NS_SEPARATOR, T_STRING])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }
            if (T_CLASS === $token[0]) {
                // Skip usage of ::class constant
                $isClassConstant = false;
                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        break;
                    }
                    if (T_DOUBLE_COLON === $tokens[$j][0]) {
                        $isClassConstant = true;
                        break;
                    } elseif (!in_array($tokens[$j][0], [T_WHITESPACE, T_DOC_COMMENT, T_COMMENT])) {
                        break;
                    }
                }
                if (!$isClassConstant) {
                    $class = true;
                }
            }
            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
