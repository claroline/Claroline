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

class AssertionManager
{
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var PlatformManager */
    private $platformManager;
    /** @var RoutingHelper */
    private $routingHelper;
    /** @var TemplateManager */
    private $templateManager;

    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $dispatcher,
        PlatformManager $platformManager,
        RoutingHelper $routingHelper,
        TemplateManager $templateManager
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->platformManager = $platformManager;
        $this->routingHelper = $routingHelper;
        $this->templateManager = $templateManager;
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

    public function delete(Assertion $assertion)
    {
        $this->om->remove($assertion);
        $this->om->flush();

        $this->dispatcher->dispatch(new RemoveBadgeEvent($assertion->getRecipient(), $assertion->getBadge()), BadgeEvents::REMOVE_BADGE);
    }

    private function notifyRecipient(Assertion $assertion)
    {
        $user = $assertion->getRecipient();
        $badge = $assertion->getBadge();
        $organization = $badge->getIssuer();

        $locale = $user->getLocale();
        $placeholders = [
            //recipient
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
            'issued_on' => $assertion->getIssuedOn()->format('d-m-Y'),
            // issuer
            'issuer_name' => $organization->getName(),
        ];

        $title = $this->templateManager->getTemplate('badge_granted', $placeholders, $locale, 'title');
        $content = $this->templateManager->getTemplate('badge_granted', $placeholders, $locale);

        $this->dispatcher->dispatch(new SendMessageEvent(
            $content,
            $title,
            [$user]
        ), MessageEvents::MESSAGE_SENDING);
    }
}
