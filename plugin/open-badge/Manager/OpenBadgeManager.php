<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OpenBadgeManager
{
    /** @var Packages */
    private $assets;

    /** @var ObjectManager */
    private $om;

    /** @var TemplateManager */
    private $templateManager;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var string */
    private $webDir;

    /**
     * OpenBadgeManager constructor.
     *
     * @param Packages         $assets
     * @param ObjectManager    $om
     * @param TemplateManager  $templateManager
     * @param WorkspaceManager $workspaceManager
     * @param string           $webDir
     */
    public function __construct(
        Packages $assets,
        ObjectManager $om,
        TemplateManager $templateManager,
        WorkspaceManager $workspaceManager,
        $webDir
    ) {
        $this->assets = $assets;
        $this->om = $om;
        $this->templateManager = $templateManager;
        $this->workspaceManager = $workspaceManager;
        $this->webDir = $webDir;
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
    }

    public function revokeAssertion(Assertion $assertion)
    {
        $assertion->setRevoked(true);
        $this->om->persist($assertion);
        $this->om->flush();
    }

    public function isAllowedBadgeManagement(TokenInterface $token, BadgeClass $badge)
    {
        $issuingMode = $badge->getIssuingMode();
        $user = $token->getUser();

        foreach ($issuingMode as $mode) {
            switch ($mode) {
              case BadgeClass::ISSUING_MODE_ORGANIZATION:
                $organization = $badge->getIssuer();

                foreach ($user->getAdministratedOrganizations() as $orga) {
                    if ($orga->getId() === $organization->getId()) {
                        return true;
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_USER:
                foreach ($badge->getAllowedIssuers() as $issuer) {
                    if ($issuer->getId() === $user->getId()) {
                        return true;
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_GROUP:
                foreach ($badge->getAllowedIssuersGroups() as $issuer) {
                    foreach ($user->getGroups() as $group) {
                        if ($issuer->getId() === $group->getId()) {
                            return true;
                        }
                    }
                }
                break;
              case BadgeClass::ISSUING_MODE_WORKSPACE:
                $workspace = $badge->getWorkspace();
                if ($workspace) {
                }
                break;
            }
        }

        return false;
    }

    public function generateCertificate(Assertion $assertion)
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
            'badge_image' => '<img src="'.$this->assets->getUrl($badge->getImage()).'" style="max-width: 100px; max-height: 50px;"/>',
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

        return $this->templateManager->getTemplate('badge_certificate', $placeholders);
    }
}
