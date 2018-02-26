<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal;

use Claroline\AppBundle\JVal\Constraint\ClarolineConstraint;
use Closure;
use JVal\Context;
use JVal\Resolver;
use JVal\Uri;

/**
 * JSON Schema validation entry point.
 */
class Validator
{
    /**
     * @var Walker
     */
    private $walker;

    /**
     * Builds a default validator instance. Accepts an optional pre-fetch
     * hook.
     *
     * @see Resolver::setPreFetchHook
     *
     * @param Closure $preFetchHook
     *
     * @return Validator
     */
    public static function build(Closure $preFetchHook = null, array $constraints = [])
    {
        $registry = new Registry($constraints);
        $resolver = new Resolver();

        if ($preFetchHook) {
            $resolver->setPreFetchHook($preFetchHook);
        }

        $walker = new Walker($registry, $resolver);

        return new self($walker);
    }

    public static function buildDefault(Closure $preFetchHook = null)
    {
        $constraints = [
            new ClarolineConstraint(),
        ];

        return self::build($preFetchHook, $constraints);
    }

    /**
     * Constructor.
     *
     * @param Walker $walker
     */
    public function __construct(Walker $walker)
    {
        $this->walker = $walker;
    }

    /**
     * Validates an instance against a given schema and returns a list
     * of violations, if any. If the schema contains relative remote
     * references, its (absolute) URI must be passed as argument.
     *
     * @param mixed    $instance
     * @param stdClass $schema
     * @param string   $schemaUri
     * @param array    $options
     *
     * @return array
     */
    public function validate($instance, \stdClass $schema, $schemaUri = '', array $options = [])
    {
        $parseContext = new Context();
        $constraintContext = new Context();
        // todo: keep ref of already resolved/parsed schemas
        $schema = $this->walker->resolveReferences($schema, new Uri($schemaUri));
        $schema = $this->walker->parseSchema($schema, $parseContext);
        $this->walker->applyConstraints($instance, $schema, $constraintContext, $options);

        return $constraintContext->getViolations();
    }
}
