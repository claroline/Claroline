<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder\Logger;

use Composer\IO\IOInterface;
use Psr\Log\AbstractLogger;

class ConsoleIoLogger extends AbstractLogger
{
    /**
     * @var IOInterface
     */
    protected $consoleIo;

    /**
     * @param IOInterface $consoleIo
     */
    public function __construct(IOInterface $consoleIo)
    {
        $this->consoleIo = $consoleIo;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function log($level, $message, array $context = array())
    {
        $this->consoleIo->write($message);
    }
}
