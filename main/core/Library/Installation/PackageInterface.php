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
interface PackageInterface
{
    public function getName();
    public function getVersion();
    public function isUpgraded();
}
