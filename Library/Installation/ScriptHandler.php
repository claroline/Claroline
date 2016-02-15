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

use Composer\IO\ConsoleIO;
use Composer\Script\Event;
use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;
use Symfony\Component\Process\Process;

class ScriptHandler
{
    const MIN_NODE_VERSION = '5.5.0.0';
    const MIN_NPM_VERSION = '3.7.0.0';

    public static function checkEnvironment(Event $event)
    {
        $io = $event->getIO();
        $parser = new VersionParser();
        static::checkNode($io, $parser);
    }

    private static function checkNode(ConsoleIO $io, VersionParser $parser)
    {
        $io->write('Checking node and npm dependencies...');

        $nodeVersion = static::getExecutableVersion(
            $parser,
            'node -v',
            'Cannot get Node.js version: node executable may be missing on your system'
        );

        if (!Comparator::greaterThanOrEqualTo($nodeVersion, self::MIN_NODE_VERSION)) {
            throw new \RuntimeException(sprintf(
                "Your Node.js version is below the minimal requirement: expected >= %s, found %s.\n" .
                "If no recent version of Node.js is available as an official package or installer for \n" .
                "your OS, consider using a version manager like nvm (http://github.com/creationix/nvm).",
                self::MIN_NODE_VERSION,
                $version
            ));
        }

        $io->write(sprintf('Node.js version: %s (OK)', $nodeVersion));

        $npmVersion = static::getExecutableVersion(
            $parser,
            'npm -v',
            'Cannot get npm version: npm executable may be missing on your system'
        );

        if (!Comparator::greaterThanOrEqualTo($npmVersion, self::MIN_NPM_VERSION)) {
            throw new \RuntimeException(sprintf(
                "Your npm version is below the minimal requirement: expected >= %s, found %s.\n" .
                "You should be able to upgrade it with: npm install -g npm@latest",
                self::MIN_NPM_VERSION,
                $npmVersion
            ));
        }

        $io->write(sprintf('npm version: %s (OK)', $npmVersion));
    }

    private static function getExecutableVersion(VersionParser $parser, $versionCmd, $notFoundMsg)
    {
        $process = new Process($versionCmd);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($notFoundMsg);
        }

        return $parser->normalize($process->getOutput());
    }
}
