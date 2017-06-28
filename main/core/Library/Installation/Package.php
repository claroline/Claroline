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
 * These are the function the OperationExecutor requires to do an update.
 * They're also implemented in the Composer Package class.
 */
class Package implements PackageInterface
{
    public function __construct($name, $version, $isUpgraded = false)
    {
        $this->name = $name;
        $this->version = $version;
        $this->isUpgraded = $isUpgraded;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function setIsUpgraded($isUpgraded)
    {
        $this->isUpgraded = $isUpgraded;
    }

    public function getIsUpgraded()
    {
        return $this->isUpgraded;
    }
}
