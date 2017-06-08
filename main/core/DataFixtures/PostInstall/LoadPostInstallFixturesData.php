<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\PostInstall;

use Claroline\CoreBundle\DataFixtures\Required\LoadRequiredFixturesData;

/**
 * Theses fixtures are loaded after the config.yml file, at the end of the installation.
 * This is only relevant for the core bundle as the process order is: Required fixtures => config.yml => PostInstall fixtures.
 */
class LoadPostInstallFixturesData extends LoadRequiredFixturesData
{
}
