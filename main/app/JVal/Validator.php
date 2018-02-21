<?php

/*
 * This file is part of the JVal package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\JVal;

use Closure;
use JVal\Registry;
use JVal\Resolver;
use JVal\Validator as JValValidator;
use JVal\Walker;

/**
 * JSON Schema validation entry point.
 */
class Validator extends JValValidator
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
    public static function buildDefault(Closure $preFetchHook = null)
    {
        $registry = new Registry();
        $resolver = new Resolver();

        if ($preFetchHook) {
            $resolver->setPreFetchHook($preFetchHook);
        }

        $walker = new Walker($registry, $resolver);

        return new self($walker);
    }
}
