<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\FunctionalEvents;
use Claroline\CoreBundle\Event\Functional\AddBadgeEvent;
use Claroline\CoreBundle\Event\Functional\RemoveBadgeEvent;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Twig\Environment;

class OpenBadgeManager
{
    /** @var ObjectManager */
    private $om;
    /** @var TemplateManager */
    private $templateManager;
    /** @var Environment */
    private $templating;
    /** @var StrictDispatcher */
    private $strictDispatcher;

    public function __construct(
        ObjectManager $om,
        TemplateManager $templateManager,
        Environment $templating,
        StrictDispatcher $strictDispatcher
    ) {
        $this->om = $om;
        $this->templateManager = $templateManager;
        $this->templating = $templating;
        $this->strictDispatcher = $strictDispatcher;
    }

    public function addAssertion(BadgeClass $badge, User $user)
    {
        $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['badge' => $badge, 'recipient' => $user]);

        if (!$assertion) {
            $assertion = new Assertion();
            $assertion->setBadge($badge);
            $assertion->setRecipient($user);
            $assertion->setImage($badge->getImage());
        }

        $assertion->setRevoked(false);

        $this->om->persist($assertion);
        $this->om->flush();

        $this->strictDispatcher->dispatch(FunctionalEvents::ADD_BADGE, AddBadgeEvent::class, [$user, $badge]);
    }

    public function revokeAssertion(Assertion $assertion)
    {
        $assertion->setRevoked(true);
        $this->om->persist($assertion);
        $this->om->flush();

        $this->strictDispatcher->dispatch(FunctionalEvents::REMOVE_BADGE, RemoveBadgeEvent::class, [$assertion->getRecipient(), $assertion->getBadge()]);
    }

    public function generateCertificate(Assertion $assertion, $basePath)
    {
        $user = $assertion->getRecipient();
        $badge = $assertion->getBadge();
        $organization = $badge->getIssuer();
        $location = 0 < count($organization->getLocations()) ? $organization->getLocations()->toArray()[0] : null;

        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'badge_name' => $badge->getName(),
            'badge_description' => $badge->getDescription(),
            'badge_image' => '<img src="'.$basePath.'/'.$badge->getImage().'" style="max-width: 100px; max-height: 50px;"/>',
            'badge_duration' => $badge->getDurationValidation(),
            'assertion_id' => $assertion->getUuid(),
            'issued_on' => $assertion->getIssuedOn()->format('d-m-Y H:i'),
            'issuer_name' => $organization->getName(),
            'issuer_email' => $organization->getEmail(),
            'issuer_phone' => $location ? $location->getPhone() : null,
            'issuer_street' => $location ? $location->getStreet() : null,
            'issuer_street_number' => $location ? $location->getStreetNumber() : null,
            'issuer_box_number' => $location ? $location->getBoxNumber() : null,
            'issuer_pc' => $location ? $location->getPc() : null,
            'issuer_town' => $location ? $location->getTown() : null,
            'issuer_country' => $location ? $location->getCountry() : null,
        ];

        $content = $this->templateManager->getTemplate('badge_certificate', $placeholders);

        return $this->templating->render('@ClarolineOpenBadge/pdf.html.twig', ['content' => $content]);
    }
}
