<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Checker used to validate the declared resources of a plugin.
 */
class ResourceChecker implements CheckerInterface
{
    const NON_EXISTENT_RESOURCE_FILE = 'non_existent_resource_file';
    const INVALID_RESOURCE_FILE_LOCATION = 'invalid_resource_file_location';
    const INVALID_RESOURCE_FILE_EXTENSION = 'invalid_resource_file_extension';
    const INVALID_YAML_RESOURCE_FILE = 'invalid_yaml_resource_file';
    const MISSING_RESOURCE_KEY = 'missing_resource_key';
    const INVALID_RESOURCE_VALUE = 'invalid_resource_value';
    const MISSING_RESOURCE_TYPE = 'missing_resource_type';
    const UNLOADABLE_RESOURCE_CLASS = 'unloadable_resource_class';
    const INVALID_RESOURCE_CLASS = 'invalid_resource_class';
    const UNLOADABLE_PARENT_RESOURCE = 'unloadable_parent_resource';
    const UNEXPECTED_RESOURCE_SMALL_ICON = 'unexpected_resource_small_icon';
    const UNEXPECTED_RESOURCE_LARGE_ICON = 'unexpected_resource_large_icon';

    private $yamlParser;
    private $plugin;
    private $pluginFqcn;
    private $errors;

    /**
     * Constructor.
     *
     * @param Yaml $yamlParser
     */
    public function __construct(Yaml $yamlParser)
    {
        $this->yamlParser = $yamlParser;
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

        if (null !== $resourceFile = $this->plugin->getCustomResourcesFile()) {
            if ($this->checkResourceFileIsLoadable($resourceFile)) {
                $this->checkRequiredKeysArePresentAndValid($resourceFile);
                $this->checkResourceDefinitions($resourceFile);
            }
        }

        return $this->errors;
    }

    private function checkResourceFileIsLoadable($resourceFile)
    {
        $isLoadable = true;
        $path = realpath($resourceFile);

        if (!file_exists($path)) {
            $isLoadable = false;
            return $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : Cannot find resource file '{$resourceFile}'.",
                self::NON_EXISTENT_RESOURCE_FILE
            );
        }

        $bundlePath = preg_quote(realpath($this->plugin->getPath()), '/');

        if (preg_match("/^{$bundlePath}/", $path) === 0) {
            $isLoadable = false;
            $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : Invalid resource file '{$path}' "
                . "(must be located within the bundle).",
                self::INVALID_RESOURCE_FILE_LOCATION
            );
        }

        if ('yml' != $ext = pathinfo($path, PATHINFO_EXTENSION)) {
            $isLoadable = false;
            $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : Unsupported '{$ext}' extension for "
                . "resource file '{$path}'(use .yml).",
                self::INVALID_RESOURCE_FILE_EXTENSION
            );
        }

        try {
            $yamlString = file_get_contents($path);
            $this->yamlParser->parse($yamlString);
        } catch (ParseException $ex) {
            $isLoadable = false;
            $this->errors[] = new ValidationError(
                "{$this->pluginFqcn} : Unloadable YAML resource file "
                . "(parse exception message : '{$ex->getMessage()}')",
                self::INVALID_YAML_RESOURCE_FILE
            );
        }

        return $isLoadable;
    }

    private function checkRequiredKeysArePresentAndValid($resourceFile)
    {
        $resources = (array) $this->yamlParser->parse($resourceFile);
        $expectedKeys = array(
            'listable' => 'boolean',
            'navigable' => 'boolean',
            'downloadable' => 'boolean'
        );

        foreach ($resources as $resource) {
            foreach ($expectedKeys as $expectedKey => $expectedType) {
                if (!isset($resource[$expectedKey])) {
                    $this->errors[] = new ValidationError(
                        "{$this->pluginFqcn} : {$expectedKey} is required in '{$resourceFile}'.",
                        self::MISSING_RESOURCE_KEY
                    );
                    continue;
                }

                if (gettype($resource[$expectedKey]) !== $expectedType) {
                    $this->errors[] = new ValidationError(
                        "{$this->pluginFqcn} : {$expectedKey} must be a {$expectedType} in '{$resourceFile}'.",
                        self::INVALID_RESOURCE_VALUE
                    );
                }
            }
        }
    }

    private function checkResourceDefinitions($resourceFile)
    {
        $resources = (array) $this->yamlParser->parse($resourceFile);

        foreach ($resources as $resourceName => $resource) {
            if (!isset($resource['class']) && !isset($resource['extends'])) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : '{$resourceName}' requires a field 'class' or a field 'extends'.",
                    self::MISSING_RESOURCE_TYPE
                );
                continue;
            }

            if (isset($resource['class'])) {
                $expectedClassLocation = $this->plugin->getPath() . '/../../'
                    . str_replace('\\', '/', $resource['class']) . '.php';

                if (!file_exists($expectedClassLocation)) {
                    $this->errors[] = new ValidationError(
                        "{$this->pluginFqcn} : {$resource['class']} (declared in {$resourceFile}) "
                        . "cannot be found (looked for {$expectedClassLocation}).",
                        self::UNLOADABLE_RESOURCE_CLASS
                    );
                    continue;
                }

                require_once $expectedClassLocation;

                $classInstance = new $resource['class'];

                if (!$classInstance instanceof AbstractResource) {
                    $this->errors[] = new ValidationError(
                        "{$this->pluginFqcn} : {$resource['class']} (declared in {$resourceFile}) "
                        . "must extend 'Claroline\\CoreBundle\\Entity\\Resource\\AbstractResource'.",
                        self::INVALID_RESOURCE_CLASS
                    );
                    continue;
                }
            }

            $this->checkExtends($resource, $resourceFile);
            $this->checkSmallIcon($resource, $resourceFile);
            $this->checkLargeIcon($resource, $resourceFile);
        }
    }

    private function checkExtends($resource, $resourceFile)
    {
        if (isset($resource['extends'])) {
            // TODO : look into registered resource in database rather than
            // looking for a class file...
            $expectedClassLocation = __DIR__ . '/../../../Entity/Resource/'
                . str_replace('\\', '/', $resource['extends']) . '.php';

            if (!file_exists($expectedClassLocation)) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : {$resource['extends']} (declared in {$resourceFile}) "
                    . "cannot be found (looked for {$expectedClassLocation}).",
                    self::UNLOADABLE_PARENT_RESOURCE
                );
            }
        }
    }

    private function checkSmallIcon($resource, $resourceFile)
    {
        if (isset($resource['small_icon'])) {
            $ds = DIRECTORY_SEPARATOR;
            $imgFolder = $this->plugin->getImgFolder();
            $expectedImgLocation = $imgFolder . $ds . 'small' . $ds . $resource['small_icon'];

            if (!file_exists($expectedImgLocation)) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : {$resource['small_icon']} (declared in {$resourceFile}) "
                    . "cannot be found (looked for {$expectedImgLocation}).",
                    self::UNEXPECTED_RESOURCE_SMALL_ICON
                );
            }
        }
    }

    private function checkLargeIcon($resource, $resourceFile)
    {
        if (isset($resource['large_icon'])) {
            $ds = DIRECTORY_SEPARATOR;
            $imgFolder = $this->plugin->getImgFolder();
            $expectedImgLocation = $imgFolder . $ds . 'large' . $ds . $resource['large_icon'];

            if (!file_exists($expectedImgLocation)) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : {$resource['large_icon']} (declared in {$resourceFile}) "
                    . "cannot be found (looked for {$expectedImgLocation}).",
                    self::UNEXPECTED_RESOURCE_LARGE_ICON
                );
            }
        }
    }
}