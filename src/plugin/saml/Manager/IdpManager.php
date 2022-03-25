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

    /**
     * @return Group[]
     */
    public function getGroups(string $idpEntityId, string $email, array $attributes): array
    {
        $groups = [];

        $conditions = $this->getConditions($idpEntityId);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (!empty($condition['groups']) && $this->checkCondition($condition, $email, $attributes)) {
                    foreach ($condition['groups'] as $groupId) {
                        $group = $this->om->getRepository(Group::class)->findOneBy(['uuid' => $groupId]);
                        if (!empty($group)) {
                            $groups[$group->getId()] = $group;
                        }
                    }

                    break;
                }
            }
        }

        // no condition met, get the default IDP groups if any
        $config = $this->getConfig($idpEntityId);
        if (empty($groups) && !empty($config['groups'])) {
            foreach ($config['groups'] as $groupId) {
                $group = $this->om->getRepository(Group::class)->findOneBy(['uuid' => $groupId]);
                if (!empty($group)) {
                    $groups[$group->getId()] = $group;
                }
            }
        }

        return array_values($groups);
    }

    public function getOrganization(string $idpEntityId, string $email, array $attributes): ?Organization
    {
        $organization = null;

        $conditions = $this->getConditions($idpEntityId);
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (!empty($condition['organization']) && $this->checkCondition($condition, $email, $attributes)) {
                    $organization = $this->om->getRepository(Organization::class)->findOneBy([
                        'uuid' => $condition['organization'],
                    ]);

                    break;
                }
            }
        }

        // no condition met, get the default IDP organization if any
        $config = $this->getConfig($idpEntityId);
        if (empty($organization) && !empty($config['organization'])) {
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

    private function getConditions(string $idpEntityId): array
    {
        $conditions = [];

        $config = $this->getConfig($idpEntityId);
        if (!empty($config['conditions'])) {
            $conditions = $config['conditions'];
        }

        return $conditions;
    }

    /**
     * Check if the IDP defines conditions to be met in order to register user to groups and organization.
     */
    private function checkCondition(array $condition, string $email, array $attributes = []): bool
    {
        // check email domain
        if (!empty($condition['email_domains'])) {
            $emailMatch = false;
            foreach ($condition['email_domains'] as $emailDomain) {
                if (false !== strpos($email, '@'.$emailDomain)) {
                    $emailMatch = true;
                    break;
                }
            }

            if (!$emailMatch) {
                return false;
            }
        }

        // check attr values
        if (!empty($condition['fields'])) {
            foreach ($condition['fields'] as $fieldName => $expectedValue) {
                if (!isset($attributes[$fieldName])) {
                    return false;
                }

                if (is_array($attributes[$fieldName])) {
                    return 0 !== count(array_filter($attributes[$fieldName], function ($attrValue) use ($expectedValue) {
                        if ((is_array($expectedValue) && !in_array($attrValue, $expectedValue)) || $attrValue !== $expectedValue) {
                            return false;
                        }

                        return true;
                    }));
                }

                if ((is_array($expectedValue) && !in_array($attributes[$fieldName], $expectedValue)) || $attributes[$fieldName] !== $expectedValue) {
                    return false;
                }
            }
        }

        return true;
    }
}
