<?php

// src/AppBundle/Routing/ExtraLoader.php
namespace Claroline\CoreBundle\Routing;

use Claroline\CoreBundle\Annotations\ApiMeta;
use Doctrine\Common\Annotations\Reader;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method as MethodConfig;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route as RouteConfig;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @DI\Service("claroline.routing.api_loader")
 * @DI\Tag("routing.loader")
 */
class ApiLoader extends Loader
{
    private $loaded = false;

    /** @var FileLocatorInterface */
    private $locator;
    /** @var ContainerInterface */
    private $container;
    /** @var Reader */
    private $reader;

    /**
     * ApiLoader constructor.
     *
     * @DI\InjectParams({
     *     "locator"   = @DI\Inject("file_locator"),
     *     "reader"    = @DI\Inject("annotation_reader"),
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param FileLocatorInterface $locator
     * @param Reader               $reader
     * @param ContainerInterface   $container
     */
    public function __construct(
        FileLocatorInterface $locator,
        Reader $reader,
        ContainerInterface $container
    ) {
        $this->locator = $locator;
        $this->container = $container;
        $this->reader = $reader;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "api" loader twice');
        }

        $path = $this->locator->locate($resource);
        $routes = new RouteCollection();
        $imported = $this->import($resource, 'annotation');
        $routes->addCollection($imported);

        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isFile()) {
                $file = $fileInfo->getPathname();

                //find prefix from annotations
                $controller = $this->findClass($file);

                if ($controller) {
                    $refClass = new \ReflectionClass($controller);
                    $class = null;
                    $found = false;
                    $prefix = '';

                    foreach ($this->reader->getClassAnnotations($refClass) as $annotation) {
                        if ($annotation instanceof ApiMeta) {
                            $found = true;
                            $class = $annotation->class;
                        }

                        if ($annotation instanceof RouteConfig) {
                            $prefix = $annotation->getPath();
                        }
                    }

                    if ($found) {
                        foreach ($this->makeRouteMap($controller, $routes, $prefix) as $name => $options) {
                            $pattern = '';

                            if ($options[0] !== '') {
                                $pattern = '/'.$options[0];
                            }

                            if ($prefix) {
                                $pattern = '/'.$prefix.$pattern;
                            }

                            $routeDefaults = [
                              '_controller' => $controller.'::'.$name.'Action',
                              'class' => $class,
                              'env' => $this->container->getParameter('kernel.environment'),
                            ];

                            $route = new Route($pattern, $routeDefaults, []);
                            $route->setMethods([$options[1]]);

                            // add the new route to the route collection:
                            $routeName = 'apiv2_'.$prefix.'_'.$this->toUnderscore($name);
                            $routes->add($routeName, $route);
                        }
                    }
                }
            }
        }

        return $routes;
    }

    private function makeRouteMap($controller, RouteCollection $routes, $prefix)
    {
        $defaults = [
          'create' => ['', 'POST'],
          'update' => ['{uuid}', 'PUT'],
          'deleteBulk' => ['', 'DELETE'],
          'list' => ['', 'GET'],
          'get' => ['get', 'GET'],
        ];

        $traits = class_uses($controller);

        foreach ($traits as $trait) {
            $refClass = new \ReflectionClass($trait);
            $methods = $refClass->getMethods();

            foreach ($methods as $method) {
                foreach ($this->reader->getMethodAnnotations($method) as $annotation) {
                    $actionName = preg_replace('/Action/', '', $method->getName());

                    if ($annotation instanceof RouteConfig) {
                        $defaults[$actionName][0] = $annotation->getPath();
                        $toRemove = $prefix.'_'.strtolower($actionName);
                        $autoName = 'claroline_core_apinew_';
                        //todo remove route from plugins but it doesn't exists yet
                        $routes->remove($autoName.$toRemove);
                    }

                    if ($annotation instanceof MethodConfig) {
                        $defaults[$actionName][1] = $annotation->getMethods()[0];
                    } else {
                        $defaults[$actionName][1] = 'GET';
                    }
                }
            }
        }

        return $defaults;
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
            if (true === $namespace && T_STRING === $token[0]) {
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
