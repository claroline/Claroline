<?php

namespace Claroline\SamlBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class IdpManager
{
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        PlatformConfigurationHandler $config,
        ObjectManager $om
    ) {
        $this->config = $config;
        $this->om = $om;
    }

    /**
     * Get the configuration of a defined IDP.
     */
    public function getConfig(string $idpEntityId): array
    {
        $idp = $this->config->getParameter('saml.idp');
        if (!empty($idp[$idpEntityId])) {
            return $idp[$idpEntityId];
        }

        return [];
    }

    public function getGroups(string $idpEntityId): array
    {
        $config = $this->getConfig($idpEntityId);

        $groups = [];
        if (!empty($config['groups'])) {
            foreach ($config['groups'] as $groupId) {
                $group = $this->om->getRepository(Group::class)->findOneBy(['uuid' => $groupId]);
                if (!empty($group)) {
                    $groups[] = $group;
                }
            }
        }

        return $groups;
    }

    public function getOrganization(string $idpEntityId): ?Organization
    {
        $config = $this->getConfig($idpEntityId);

        $organization = null;
        if (!empty($config['organization'])) {
            $organization = $this->om->getRepository(Organization::class)->findOneBy([
                'uuid' => $config['organization'],
            ]);
        }

        return $organization;
    }

    public function getFieldMapping(string $idpEntityId): array
    {
        $mapping = [
            'email' => 'iam-email',
            'firstName' => 'iam-firstname',
            'lastName' => 'iam-lastname',
        ];

        $config = $this->getConfig($idpEntityId);
        if (!empty($config['mapping'])) {
            $mapping = array_merge($mapping, $config['mapping']);
        }

        return $mapping;
    }

    public function getEmailDomains(string $idpEntityId): array
    {
        $domains = [];

        $config = $this->getConfig($idpEntityId);
        if (!empty($config['email_domains'])) {
            $domains = $config['email_domains'];
        }

        return $domains;
    }

    /**
     * Check if an email matches one of the domains defined in the IDP.
     * Only users with matching emails are registered to the IDP groups and organization.
     */
    public function checkEmail(string $idpEntityId, string $email): bool
    {
        $emailDomains = $this->getEmailDomains($idpEntityId);
        if (empty($emailDomains)) {
            return true;
        }

        foreach ($emailDomains as $emailDomain) {
            if (false !== strpos($email, '@'.$emailDomain)) {
                return true;
            }
        }

        return false;
    }
}
