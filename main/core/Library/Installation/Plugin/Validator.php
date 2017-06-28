<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use InvalidArgumentException;

/**
 * This class is used to perform various validation checks upon a plugin,
 * calling dedicated checkers. If the validation succeed, the plugin could
 * be considered as safe to install by the plugin installer.
 *
 * Note: this class is defined as a service in config/services.yml (array injection is
 * not supported by the di extra bundle).
 */
class Validator
{
    private $checkers;
    private $pluginConfiguration;
    private $updateMode;

    /**
     * Constructor.
     *
     * @param CheckerInterface[] $checkers
     *
     * @throws \InvalidArgumentException
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
        $this->updateMode = false;
    }

    /**
     * Validates a plugin.
     *
     * @param DistributionPluginBundle $plugin
     *
     * @return array[ValidationError]
     */
    public function validate(DistributionPluginBundle $plugin)
    {
        $validationErrors = [];

        foreach ($this->checkers as $checker) {
            if (null !== $errors = $checker->check($plugin, $this->isInUpdateMode())) {
                $validationErrors = array_merge($validationErrors, $errors);
                continue;
            }

            if ($checker instanceof ConfigurationChecker) {
                $this->pluginConfiguration = $checker->getProcessedConfiguration();
            }
        }

        return $validationErrors;
    }

    /**
     * @return mixed
     */
    public function getPluginConfiguration()
    {
        return $this->pluginConfiguration;
    }

    /**
     * @return Validator
     */
    public function activeUpdateMode()
    {
        $this->updateMode = true;

        return $this;
    }

    /**
     * @return Validator
     */
    public function deactivateUpdateMode()
    {
        $this->updateMode = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInUpdateMode()
    {
        return $this->updateMode;
    }
}
