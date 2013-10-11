<?php

namespace Claroline\BundleRecorder;

class Operation
{
    const INSTALL = 'install';
    const UPDATE = 'update';
    const UNINSTALL = 'uninstall';
    const BUNDLE_CORE = 'core';
    const BUNDLE_PLUGIN = 'plugin';

    private $type;
    private $bundleFqcn;
    private $bundleType;
    private $fromVersion;
    private $toVersion;

    public function __construct($type, $bundleFqcn, $bundleType)
    {
        if (!in_array($type, array(self::INSTALL, self::UPDATE, self::UNINSTALL))) {
            throw new \InvalidArgumentException(
                'Operation type must be an Operation::* class constant'
            );
        }

        if ($bundleType !== self::BUNDLE_CORE && $bundleType !== self::BUNDLE_PLUGIN) {
            throw new \InvalidArgumentException(
                'Bundle type must be an Operation::BUNDLE_* class constant'
            );
        }

        $this->type = $type;
        $this->bundleFqcn = $bundleFqcn;
        $this->bundleType = $bundleType;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBundleFqcn()
    {
        return $this->bundleFqcn;
    }

    public function getBundleType()
    {
        return $this->bundleType;
    }

    public function setFromVersion($version)
    {
        $this->fromVersion = $version;
    }

    public function getFromVersion()
    {
        return $this->fromVersion;
    }

    public function setToVersion($version)
    {
        $this->toVersion = $version;
    }

    public function getToVersion()
    {
        return $this->toVersion;
    }
}
