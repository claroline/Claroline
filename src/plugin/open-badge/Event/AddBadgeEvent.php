<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Event;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a user obtains a new badge (aka a new Assertion entity is created).
 */
class AddBadgeEvent extends AbstractBadgeEvent
{
}
