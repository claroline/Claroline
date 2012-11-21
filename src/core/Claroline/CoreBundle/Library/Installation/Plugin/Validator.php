<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \InvalidArgumentException;
use Claroline\CoreBundle\Library\PluginBundle;

/**
 * This class is used to perform various validation checks upon a plugin,
 * calling dedicated checkers. If the validation succeed, the plugin could
 * be considered as safe to install by the plugin installer.
 */
class Validator
{
    private $checkers;

    /**
     * Constructor.
     *
     * @param array $checkers[CheckerInterface]
     */
    public function __construct(array $checkers)
    {
        foreach ($checkers as $checker) {
            if (!$checker instanceof CheckerInterface) {
                throw new InvalidArgumentException(
                    'Instances of CheckerInterface expected'
                );
            }
        }

        $this->checkers = $checkers;
    }

    /**
     * Validates a plugin.
     *
     * @param PluginBundle $plugin
     *
     * @return array[ValidationError]
     */
    public function validate(PluginBundle $plugin)
    {
        $validationErrors = array();

        foreach ($this->checkers as $checker) {
            $errors = $checker->check($plugin);
            if (null !== $errors = $checker->check($plugin)) {
                $validationErrors = array_merge($validationErrors, $errors);
            }
        }
//        var_dump($validationErrors);
        return $validationErrors;
    }
}