<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Library\PluginBundle;

/**
 * Checker used to validate the required properties of a plugin.
 */
class BaseChecker implements CheckerInterface
{
    const INVALID_FQCN = 'invalid_fqcn';
    const INVALID_TRANSLATION_KEY = 'invalid_translation_key';

    private $plugin;
    private $pluginFqcn;
    private $errors;

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
        $this->checkPluginFollowsFQCNConvention();
        $this->checkTranslationKeysAreValid();

        return $this->errors;
    }

    private function checkPluginFollowsFQCNConvention()
    {
        $nameParts = explode('\\', $this->pluginFqcn);

        if (count($nameParts) !== 3 || $nameParts[2] !== $nameParts[0] . $nameParts[1]) {
            $this->errors[] = new ValidationError(
                "Plugin FQCN '{$this->pluginFqcn}' doesn't follow the "
                . "'Vendor\BundleName\VendorBundleName' convention.",
                self::INVALID_FQCN
            );
        }
    }

    private function checkTranslationKeysAreValid()
    {
        $keys = array();
        $keys['name'] = $this->plugin->getNameTranslationKey();
        $keys['description'] = $this->plugin->getDescriptionTranslationKey();

        foreach ($keys as $type => $key) {
            if (!is_string($key)) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : {$type} translation key must be a string.",
                    self::INVALID_TRANSLATION_KEY
                );
            }

            if (empty($key)) {
                $this->errors[] = new ValidationError(
                    "{$this->pluginFqcn} : {$type} translation key cannot be empty.",
                    self::INVALID_TRANSLATION_KEY
                );
            }
        }
    }
}