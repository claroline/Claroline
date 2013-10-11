<?php

namespace Claroline\BundleRecorder;

class Operation
{
    const INSTALL = 'install';
    const UPDATE = 'update';
    const UNINSTALL = 'uninstall';

    private $type;
    private $bundleFqcn;
    private $fromVersion;
    private $toVersion;

    public function __construct($type, $bundleFqcn)
    {
        if (!in_array($type, array(self::INSTALL, self::UPDATE, self::UNINSTALL))) {
            throw new \InvalidArgumentException(
                'Operation type must be an Operation::* class constant'
            );
        }

        $this->type = $type;
        $this->bundleFqcn = $bundleFqcn;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBundleFqcn()
    {
        return $this->bundleFqcn;
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
