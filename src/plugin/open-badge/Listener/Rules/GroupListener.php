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
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

class GroupListener
{
    /** @var ObjectManager */
    private $om;

    /** @var TranslatorInterface */
    private $translator;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RuleManager */
    private $manager;

    /**
     * RuleListener constructor.
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator,
        TokenStorageInterface $tokenStorage,
        RuleManager $manager
    ) {
        $this->om = $om;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    public function onUserPatch(PatchEvent $event)
    {
        if (Crud::COLLECTION_ADD === $event->getAction() && 'group' === $event->getProperty()) {
            /** @var Rule[] $rules */
            $rules = $this->om->getRepository(Rule::class)->findBy(['group' => $event->getValue()]);

            foreach ($rules as $rule) {
                $this->awardInGroup($event->getObject(), $rule);
            }
        }
    }

    public function onGroupPatch(PatchEvent $event)
    {
        if (Crud::COLLECTION_ADD === $event->getAction() && 'user' === $event->getProperty()) {
            /** @var Rule[] $rules */
            $rules = $this->om->getRepository(Rule::class)->findBy(['group' => $event->getObject()]);

            foreach ($rules as $rule) {
                $this->awardInGroup($event->getValue(), $rule);
            }
        }
    }

    private function awardInGroup(User $user, Rule $rule)
    {
        $evidence = new Evidence();
        $now = new \DateTime();
        $evidence->setNarrative($this->translator->trans(
            'evidence_narrative_add_group',
            [
                '%doer%' => $this->tokenStorage->getToken()->getUser()->getUsername(),
                '%date%' => $now->format('Y-m-d H:i:s'),
            ],
            'badge'
        ));
        $evidence->setRule($rule);
        $evidence->setName(Rule::IN_GROUP);
        $evidence->setUser($user);

        $this->om->persist($evidence);
        $this->om->flush();

        $this->manager->verifyAssertion($user, $rule->getBadge());
    }
}
