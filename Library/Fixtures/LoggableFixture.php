<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Fixtures;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Psr\Log\LoggerInterface;

abstract class LoggableFixture extends AbstractFixture
{
    use LoggableTrait;
}
