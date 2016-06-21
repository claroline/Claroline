<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Composer;

use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Output\StreamOutput;

class FileIO extends ConsoleIO
{
    protected $output;
    protected $lastMessage;

    public function __construct($logFile)
    {
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $this->output = new StreamOutput(fopen($logFile, 'a'));
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = true)
    {
        $this->output->write($messages, $newline);
        $this->lastMessage = implode($newline ? "\n" : '', (array) $messages);
    }

    /**
     * {@inheritdoc}
     */
    public function ask($question, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askConfirmation($question, $default = true)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askAndValidate($question, $validator, $attempts = false, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function askAndHideAnswer($question)
    {
        return $default;
    }
}
