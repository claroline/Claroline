<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

/**
 * Holds the details of an install/update operation, such as the type
 * of the operation, the original package, the target version, etc.
 */
class Operation
{
    const INSTALL = 'install';
    const UPDATE = 'update';

    private $type;
    private $package;
    private $bundleFqcn;
    private $fromVersion;
    private $toVersion;

    public function __construct($type, $package, $bundleFqcn)
    {
        if (!in_array($type, [self::INSTALL, self::UPDATE])) {
            throw new \InvalidArgumentException(
                'Operation type must be an Operation::* class constant'
            );
        }

        $this->type = $type;
        $this->package = $package;
        $this->bundleFqcn = $bundleFqcn;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPackage()
    {
        return $this->package;
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
