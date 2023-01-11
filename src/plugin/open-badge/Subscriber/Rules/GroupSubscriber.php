<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Subscriber\Rules;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GroupSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var RuleManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        RuleManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('patch', 'post', User::class) => 'onUserPatch',
            Crud::getEventName('patch', 'post', Group::class) => 'onGroupPatch',
        ];
    }

    public function onUserPatch(PatchEvent $event): void
    {
        if (Crud::COLLECTION_ADD === $event->getAction() && 'group' === $event->getProperty()) {
            /** @var Rule[] $rules */
            $rules = $this->om->getRepository(Rule::class)->findBy(['group' => $event->getValue()]);

            foreach ($rules as $rule) {
                $this->manager->grant($rule, $event->getObject());
            }
        }
    }

    public function onGroupPatch(PatchEvent $event): void
    {
        if (Crud::COLLECTION_ADD === $event->getAction() && 'user' === $event->getProperty()) {
            /** @var Rule[] $rules */
            $rules = $this->om->getRepository(Rule::class)->findBy(['group' => $event->getObject()]);

            foreach ($rules as $rule) {
                $this->manager->grant($rule, $event->getValue());
            }
        }
    }
}
