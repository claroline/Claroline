<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Logger;

use Symfony\Component\Console\Logger\ConsoleLogger as SfLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger
{
    public static function get(OutputInterface $output)
    {
        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        );

        $formatLevelMap = array(
            LogLevel::DEBUG => SfLogger::ERROR,
            LogLevel::NOTICE => SfLogger::INFO,
        );

        $consoleLogger = new SfLogger($output, $verbosityLevelMap, $formatLevelMap);

        return $consoleLogger;
    }
}
