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

use Claroline\CoreBundle\Entity\Log\FunctionalLog;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ResourceSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [

        ];
    }
}
