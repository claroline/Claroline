<?php

namespace Claroline\InstallationBundle\Updater;

use Claroline\AppBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;

abstract class Updater implements LoggerAwareInterface
{
    use LoggableTrait;
}
