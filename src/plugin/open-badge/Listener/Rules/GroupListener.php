<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Listener\Rules;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;

class GroupListener
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

    public function onUserPatch(PatchEvent $event)
    {
        if (Crud::COLLECTION_ADD === $event->getAction() && 'group' === $event->getProperty()) {
            /** @var Rule[] $rules */
            $rules = $this->om->getRepository(Rule::class)->findBy(['group' => $event->getValue()]);

            foreach ($rules as $rule) {
                $this->manager->grant($rule, $event->getObject());
            }
        }
    }

    public function onGroupPatch(PatchEvent $event)
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
