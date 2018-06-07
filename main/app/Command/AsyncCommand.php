<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Command;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Process\Process;

/**
 * @DI\Service("claroline.async.command")
 */
class AsyncCommand
{
    /**
     * @DI\InjectParams({
     *     "rootDir"  = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function run($commandName)
    {
        $process = new Process($this->getCommandName($commandName));
        $process->start();
    }

    public function getCommandName($commandName)
    {
        return 'php '.$this->rootDir.'/console '.$commandName;
    }
}
