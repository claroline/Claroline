<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Subscriber\Rule;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class WorkspaceSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [

        ];
    }
}
