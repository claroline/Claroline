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
        'Claroline\\AppBundle\\JVal\\Constraint\\Maximum',
        'Claroline\\AppBundle\\JVal\\Constraint\\Minimum',
        'Claroline\\AppBundle\\JVal\\Constraint\\MaxLength',
        'Claroline\\AppBundle\\JVal\\Constraint\\MinLength',
        'Claroline\\AppBundle\\JVal\\Constraint\\Pattern',
        'Claroline\\AppBundle\\JVal\\Constraint\\Items',
        'Claroline\\AppBundle\\JVal\\Constraint\\MaxItems',
        'Claroline\\AppBundle\\JVal\\Constraint\\MinItems',
        'Claroline\\AppBundle\\JVal\\Constraint\\UniqueItems',
        'Claroline\\AppBundle\\JVal\\Constraint\\Required',
        'Claroline\\AppBundle\\JVal\\Constraint\\Properties',
        'Claroline\\AppBundle\\JVal\\Constraint\\Dependencies',
        'Claroline\\AppBundle\\JVal\\Constraint\\Enum',
        'Claroline\\AppBundle\\JVal\\Constraint\\Type',
        'Claroline\\AppBundle\\JVal\\Constraint\\Format',
    ];

    private static $draft4Constraints = [
        'Claroline\\AppBundle\\JVal\\Constraint\\MultipleOf',
        'Claroline\\AppBundle\\JVal\\Constraint\\MinProperties',
        'Claroline\\AppBundle\\JVal\\Constraint\\MaxProperties',
        'Claroline\\AppBundle\\JVal\\Constraint\\AllOf',
        'Claroline\\AppBundle\\JVal\\Constraint\\AnyOf',
        'Claroline\\AppBundle\\JVal\\Constraint\\OneOf',
        'Claroline\\AppBundle\\JVal\\Constraint\\Not',
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
                throw new UnsupportedVersionException("Schema version '{$version}' not supported");
        }
    }

    private function createBuiltInConstraints(array $constraintNames)
    {
        return array_map(function ($name) {
            $class = "{$name}Constraint";

            return new $class();
        }, $constraintNames);
    }
}
