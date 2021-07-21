<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\OrganizationSerializer;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CursusBundle\Entity\Quota;

class QuotaSerializer
{
    use SerializerTrait;

    /** @var OrganizationSerializer */
    private $organizationSerializer;

    /** @var OrganizationRepository */
    private $organizationRepo;

    public function __construct(ObjectManager $om, OrganizationSerializer $organizationSerializer)
    {
        $this->organizationSerializer = $organizationSerializer;
        $this->organizationRepo = $om->getRepository(Organization::class);
    }

    public function getSchema()
    {
        return '#/plugin/cursus/quota.json';
    }

    public function serialize(Quota $quota, array $options = []): array
    {
        $serialized = [
            'id' => $quota->getUuid(),
            'organization' => $this->organizationSerializer->serialize($quota->getOrganization(), [Options::SERIALIZE_MINIMAL]),
            'threshold' => $quota->getThreshold()
        ];

        return $serialized;
    }

    public function deserialize(array $data, Quota $quota): Quota
    {
        $this->sipe('id', 'setUuid', $data, $quota);
        $this->sipe('threshold', 'setThreshold', $data, $quota);

        if (isset($data['organization'])) {
            $organization = $this->organizationRepo->findOneBy(['uuid' => $data['organization']['id']]);
            $quota->setOrganization($organization);
        }

        return $quota;
    }
}
