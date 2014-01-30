<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Maintenance;

use Symfony\Component\Yaml\Yaml;

class MaintenanceHandler {

    const REL_PATH = '/../../../../../../../app/config/platform_options.yml';

    public static function enableMaintenance()
    {
        $options = Yaml::parse(__DIR__ . self::REL_PATH);
        $options['maintenance_mode'] = true;
        $yaml = Yaml::dump($options);
        file_put_contents(__DIR__ . self::REL_PATH, $yaml);
    }

    public static function disableMaintenance()
    {
        $options = Yaml::parse(__DIR__ . self::REL_PATH);
        $options['maintenance_mode'] = false;
        $yaml = Yaml::dump($options);
        file_put_contents(__DIR__ . self::REL_PATH, $yaml);
    }

} 