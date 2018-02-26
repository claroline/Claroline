<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal;

use JVal\Exception\UnsupportedVersionException;
use JVal\Registry as JValRegistry;

/**
 * Stores and exposes validation constraints per version.
 */
class Registry extends JValRegistry
{
    private static $commonConstraints = [
        'Maximum',
        'Minimum',
        'MaxLength',
        'MinLength',
        'Pattern',
        'Items',
        'MaxItems',
        'MinItems',
        'UniqueItems',
        'Required',
        'Properties',
        'Dependencies',
        'Enum',
        'Type',
        'Format',
    ];

    private static $draft4Constraints = [
        'MultipleOf',
        'MinProperties',
        'MaxProperties',
        'AllOf',
        'AnyOf',
        'OneOf',
        'Not',
    ];

    private $customConstraints;

    public function __construct(array $constraints = [])
    {
        $this->customConstraints = $constraints;
    }

    /**
     * Loads the constraints associated with a given JSON Schema version.
     *
     * @param string $version
     *
     * @return Constraint[]
     *
     * @throws UnsupportedVersionException if the version is not supported
     */
    protected function createConstraints($version)
    {
        switch ($version) {
            case self::VERSION_CURRENT:
            case self::VERSION_DRAFT_4:
                return array_merge($this->createBuiltInConstraints(
                    array_merge(
                        self::$commonConstraints,
                        self::$draft4Constraints
                    )
                ), $this->customConstraints);
            default:
                throw new UnsupportedVersionException(
                    "Schema version '{$version}' not supported"
                );
        }
    }

    private function createBuiltInConstraints(array $constraintNames)
    {
        return array_map(function ($name) {
            $class = "JVal\\Constraint\\{$name}Constraint";

            return new $class();
        }, $constraintNames);
    }
}
