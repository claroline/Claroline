<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Messenger\Message\GrantBadge;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

class BadgeManager
{
    public function __construct(
        private readonly TemplateManager $templateManager,
        private readonly Environment $templating,
        private readonly MessageBusInterface $messageBus,
        private readonly PlatformManager $platformManager
    ) {
    }

    public function generateCertificate(Assertion $assertion): string
    {
        $user = $assertion->getRecipient();
        $badge = $assertion->getBadge();
        $organization = $badge->getIssuer();

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
                'assertion_id' => $assertion->getUuid(),
                // issuer
                'issuer_name' => $organization ? $organization->getName() : '',
                'issuer_email' => $organization ? $organization->getEmail() : '',
            ],
            $this->templateManager->formatDatePlaceholder('issued_on', $assertion->getIssuedOn())
        );

        if ($badge->getTemplate()) {
            $content = $this->templateManager->getTemplateContent($badge->getTemplate(), $placeholders);
        } else {
            // use default template
            $content = $this->templateManager->getTemplate('badge_certificate', $placeholders);
        }

        return $this->templating->render('@ClarolineOpenBadge/pdf.html.twig', ['content' => $content]);
    }

    public function grantAll(BadgeClass $badge): void
    {
        $this->messageBus->dispatch(new GrantBadge($badge->getId()));
    }
}
