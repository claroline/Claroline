<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Messenger\Message\GrantBadge;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

class BadgeManager
{
    /** @var TemplateManager */
    private $templateManager;
    /** @var Environment */
    private $templating;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var PlatformManager */
    private $platformManager;

    public function __construct(
        TemplateManager $templateManager,
        Environment $templating,
        MessageBusInterface $messageBus,
        PlatformManager $platformManager
    ) {
        $this->templateManager = $templateManager;
        $this->templating = $templating;
        $this->messageBus = $messageBus;
        $this->platformManager = $platformManager;
    }

    public function generateCertificate(Assertion $assertion)
    {
        $user = $assertion->getRecipient();
        $badge = $assertion->getBadge();
        $organization = $badge->getIssuer();

        /** @var Location $location */
        $location = 0 < count($organization->getLocations()) ? $organization->getLocations()->toArray()[0] : null;

        $placeholders = [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'username' => $user->getUsername(),
            'badge_name' => $badge->getName(),
            'badge_description' => $badge->getDescription(),
            'badge_image' => '<img src="'.$this->platformManager->getUrl().'/'.$badge->getImage().'" style="max-width: 100px; max-height: 50px;"/>',
            'badge_image_url' => $this->platformManager->getUrl().'/'.$badge->getImage(),
            'badge_duration' => $badge->getDurationValidation(),
            'assertion_id' => $assertion->getUuid(),
            'issued_on' => $assertion->getIssuedOn()->format('d-m-Y'),
            'issuer_name' => $organization->getName(),
            'issuer_email' => $organization->getEmail(),
            'issuer_phone' => $location ? $location->getPhone() : null,
            'issuer_street' => $location ? $location->getAddressStreet1().' '.$location->getAddressStreet2() : null,
            'issuer_pc' => $location ? $location->getAddressPostalCode() : null,
            'issuer_town' => $location ? $location->getAddressCity() : null,
            'issuer_country' => $location ? $location->getAddressCountry() : null,
        ];

        if ($badge->getTemplate()) {
            $content = $this->templateManager->getTemplateContent($badge->getTemplate(), $placeholders);
        } else {
            // use default template
            $content = $this->templateManager->getTemplate('badge_certificate', $placeholders);
        }

        return $this->templating->render('@ClarolineOpenBadge/pdf.html.twig', ['content' => $content]);
    }

    public function grantAll(BadgeClass $badge)
    {
        $this->messageBus->dispatch(new GrantBadge($badge->getUuid()));
    }
}
