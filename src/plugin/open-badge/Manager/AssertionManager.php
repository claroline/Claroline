<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Event\AddBadgeEvent;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;
use Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AssertionManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly PlatformManager $platformManager,
        private readonly RoutingHelper $routingHelper,
        private readonly TemplateManager $templateManager,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Checks if a User meets a Badge requirements by checking Evidence for each rule and grants him if needed.
     */
    public function grant(BadgeClass $badge, User $user): ?Assertion
    {
        $isGranted = true;
        $badgeRules = $badge->getRules();

        // check if there are evidence for each badge rule
        foreach ($badgeRules as $badgeRule) {
            $evidences = $this->om->getRepository(Evidence::class)->findBy(['user' => $user, 'rule' => $badgeRule]);
            if (0 === count($evidences)) {
                $isGranted = false;
                break; // no need to continue, user can not have the badge for now
            }
        }

        if ($isGranted) {
            // link evidences to this assertion
            $evidences = [];
            foreach ($badgeRules as $badgeRule) {
                $evidences = array_merge($evidences, $this->om->getRepository(Evidence::class)->findBy(['user' => $user, 'rule' => $badgeRule]));
            }

            return $this->create($badge, $user, $evidences);
        }

        return null;
    }

    public function create(BadgeClass $badge, User $user, array $evidences = []): Assertion
    {
        $newlyGranted = false;
        $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['badge' => $badge, 'recipient' => $user]);
        if (!$assertion) {
            $assertion = new Assertion();
            $assertion->setBadge($badge);
            $assertion->setRecipient($user);
            $assertion->setImage($badge->getImage());

            $newlyGranted = true;
        }

        $assertion->setRevoked(false);

        if (!empty($evidences)) {
            foreach ($evidences as $evidence) {
                $evidence->setAssertion($assertion);
                $this->om->persist($evidence);
            }
        }

        $this->om->persist($assertion);
        $this->om->flush();

        if ($newlyGranted && $badge->getNotifyGrant()) {
            $this->notifyRecipient($assertion);
        }

        $this->dispatcher->dispatch(new AddBadgeEvent($user, $badge), BadgeEvents::ADD_BADGE);

        return $assertion;
    }

    public function revoke(Assertion $assertion): Assertion
    {
        $assertion->setRevoked(true);

        $this->om->persist($assertion);
        $this->om->flush();

        $this->dispatcher->dispatch(new RemoveBadgeEvent($assertion->getRecipient(), $assertion->getBadge()), BadgeEvents::REMOVE_BADGE);

        return $assertion;
    }

    public function delete(Assertion $assertion): void
    {
        $this->om->remove($assertion);
        $this->om->flush();

        $this->dispatcher->dispatch(new RemoveBadgeEvent($assertion->getRecipient(), $assertion->getBadge()), BadgeEvents::REMOVE_BADGE);
    }

    private function notifyRecipient(Assertion $assertion): void
    {
        $user = $assertion->getRecipient();
        $badge = $assertion->getBadge();
        $organization = $badge->getIssuer();

        $locale = $user->getLocale();
        $placeholders = array_merge([
                // recipient
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
                // badge
                'badge_name' => $badge->getName(),
                'badge_description' => $badge->getDescription(),
                'badge_image' => '<img src="'.$this->platformManager->getUrl().'/'.$badge->getImage().'" style="max-width: 100px; max-height: 50px;"/>',
                'badge_image_url' => $this->platformManager->getUrl().'/'.$badge->getImage(),
                'badge_duration' => $badge->getDurationValidation(),
                // assertion
                'assertion_url' => $this->routingHelper->desktopUrl('badges')."/badges/{$badge->getUuid()}/assertion/{$assertion->getUuid()}",
                // issuer
                'issuer_name' => $organization->getName(),
            ],
            $this->templateManager->formatDatePlaceholder('issued_on', $assertion->getIssuedOn())
        );

        $title = $this->templateManager->getTemplate('badge_granted', $placeholders, $locale, 'title');
        $content = $this->templateManager->getTemplate('badge_granted', $placeholders, $locale);

        $this->dispatcher->dispatch(new SendMessageEvent(
            $content,
            $title,
            [$user]
        ), MessageEvents::MESSAGE_SENDING);
    }

    public function transferBadgesAction(User $userFrom, User $userTo): void
    {
        $assertions = $this->om->getRepository(Assertion::class)->findBy(['recipient' => $userFrom]);

        foreach ($assertions as $assert) {
            $assertions = $this->om->getRepository(Assertion::class)->findBy(['recipient' => $userTo, 'badge' => $assert->getBadge()]);

            if (count($assertions) > 0) {
                $this->om->remove($assert);
                continue;
            }

            foreach ($assert->getEvidences() as $evidence) {
                if (null === $evidence) {
                    continue;
                }

                $evidence->setWorkspaceEvidence(null);
                $evidence->setResourceEvidence(null);
                $evidence->setUser($userTo);
                $this->om->persist($evidence);
            }

            $assert->setNarrative($this->translator->trans('transferred', [], 'badge'));
            $assert->setRecipient($userTo);
            $this->om->persist($assert);
        }

        $this->om->flush();
    }
}
