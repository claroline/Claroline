<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Claroline\CoreBundle\Library\PluginBundle;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Checker used to validate the routing of a plugin.
 *
 * @DI\Service("claroline.plugin.routing_checker")
 */
class RoutingChecker implements CheckerInterface
{
    const INVALID_ROUTING_PREFIX = 'invalid_routing_prefix';
    const ALREADY_REGISTERED_PREFIX = 'already_registered_routing_prefix';
    const NON_EXISTENT_ROUTING_FILE = 'non_existent_routing_file';
    const INVALID_ROUTING_LOCATION = 'invalid_routing_location';
    const INVALID_ROUTING_EXTENSION = 'invalid_routing_extension';
    const INVALID_YAML_ROUTING_FILE = 'invalid_yaml_routing_file';

    private $router;
    private $yamlParser;
    private $mainPluginRoutingFile;
    private $plugin;
    private $pluginFqcn;
    private $errors;

    /**
     * Constructor.
     *
     * @param Router $router
     * @param Yaml   $yamlParser
     *
     * @DI\InjectParams({
     *     "router" = @DI\Inject("router"),
     *     "yamlParser" = @DI\Inject("symfony.yaml"),
     *     "mainPluginRoutingFile" = @DI\Inject("%kernel.root_dir%/config/local/plugin/routing.yml")
     * })
     */
    public function __construct(Router $router, Yaml $yamlParser, $mainPluginRoutingFile)
    {
        $this->router = $router;
        $this->yamlParser = $yamlParser;
        $this->mainPluginRoutingFile = $mainPluginRoutingFile;
    }

    /**
     * {@inheritDoc}
     *
     * @param PluginBundle $plugin
     */
    public function check(PluginBundle $plugin)
    {
        $this->plugin = $plugin;
        $this->pluginFqcn = get_class($plugin);
        $this->errors = array();
        $this->checkRoutingPrefixIsValid();
        count($this->errors) === 0 && $this->checkRoutingPrefixIsNotAlreadyRegistered();
        $this->checkRoutingResourcesAreLoadable();

        return $this->errors;
    }

    private function checkRoutingPrefixIsValid()
    {
        $prefix = $this->plugin->getRoutingPrefix();

        if (!is_string($prefix)) {
            return $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : routing prefix must be a string.",
                self::INVALID_ROUTING_PREFIX
            );
        }

        if (empty($prefix)) {
            return $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : routing prefix cannot be empty.",
                self::INVALID_ROUTING_PREFIX
            );
        }

        if (preg_match('#\s#', $prefix)) {
            return $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : routing prefix cannot contain white spaces.",
                self::INVALID_ROUTING_PREFIX
            );
        }
    }

    private function checkRoutingPrefixIsNotAlreadyRegistered()
    {
        // As of Symfony 2.2, there is no way to retrieve the prefixes already in use in
        // the route collection. The following code relies on possible modifications of
        // the RouteCollection class in the Routing component. To be uncommented if those
        // changes are accepted.
        /*
        $registeredPrefixes = $this->router->getRouteCollection()->getPrefixes();
        $pluginPrefix = $this->plugin->getRoutingPrefix();

        if (in_array($pluginPrefix, $registeredPrefixes)) {
            $resources = (array) $this->yamlParser->parse(file_get_contents($this->mainPluginRoutingFile));
            $isConflictingWithPluginPrefix = false;

            foreach ($resources as $bundleKey => $resource) {
                if ('/' . $resource['prefix'] === $pluginPrefix) {
                    $isConflictingWithPluginPrefix = true;

                    if (0 !== strpos($bundleKey, $this->plugin->getName())) {
                        $this->errors[] = new ValidationError(
                            "{$this->pluginFqcn} : routing prefix '{$pluginPrefix}' "
                            . "is already registered by another plugin.",
                            self::ALREADY_REGISTERED_PREFIX
                        );
                        break;
                    }
                }
            }

            if (!$isConflictingWithPluginPrefix) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : routing prefix '{$pluginPrefix}' is already "
                    . "registered by the core routing.",
                    self::ALREADY_REGISTERED_PREFIX
                );
            }
        }
        */
    }

    private function checkRoutingResourcesAreLoadable()
    {
        $paths = $this->plugin->getRoutingResourcesPaths();

        if ($paths === null) {
            return;
        }

        foreach ((array) $paths as $path) {
            $path = realpath($path);

            if (!file_exists($path)) {
                return $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : Cannot find routing file '{$path}'.",
                    self::NON_EXISTENT_ROUTING_FILE
                );
            }

            $bundlePath = preg_quote(realpath($this->plugin->getPath()), '/');

            if (preg_match("/^{$bundlePath}/", $path) === 0) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : Invalid routing file '{$path}' "
                    . "(must be located within the bundle).",
                    self::INVALID_ROUTING_LOCATION
                );
            }

            if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION)) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : Unsupported '{$ext}' extension for "
                    . "routing file '{$path}'(use .yml).",
                    self::INVALID_ROUTING_EXTENSION
                );
            }

            try {
                $yamlString = file_get_contents($path);
                $this->yamlParser->parse($yamlString);
            } catch (ParseException $ex) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : Unloadable YAML routing file "
                    . "(parse exception message : '{$ex->getMessage()}')",
                    self::INVALID_YAML_ROUTING_FILE
                );
            }
        }
    }
}