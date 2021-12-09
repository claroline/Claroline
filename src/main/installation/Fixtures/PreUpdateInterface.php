<?php

namespace Claroline\InstallationBundle\Fixtures;

/**
 * PreUpdate fixtures are loaded at each install/update just after the bundle migrations are executed
 * and before plugin config is processed.
 * You MUST ensure the data don't exist before inserting anything.
 */
interface PreUpdateInterface
{
}
