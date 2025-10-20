<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\OAuthClient;
use Claroline\CommunityBundle\Serializer\OrganizationSerializer;
use Claroline\CoreBundle\Entity\Organization\Organization;

class OAuthClientSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly OrganizationSerializer $organizationSerializer
    ) {
    }

    public function getClass(): string
    {
        return OAuthClient::class;
    }

    public function serialize(OAuthClient $client): array
    {
        // do not display full client secret in API
        $clientSecretDisplay = substr($client->getClientSecret(), -8);

        return [
            'id' => $client->getUuid(),
            'serviceProvider' => $client->getServiceProvider(),
            'name' => $client->getName(),
            'clientId' => $client->getClientId(),
            'clientSecretDisplay' => '****'.$clientSecretDisplay,
            'urlAuthorize' => $client->getUrlAuthorize(),
            'urlAccessToken' => $client->getUrlAccessToken(),
            'urlResourceOwnerDetails' => $client->getUrlResourceOwnerDetails(),
            'additionalParameters' => !empty($client->getAdditionalParameters()) ? $client->getAdditionalParameters() : null,
            'fieldsMapping' => !empty($client->getFieldsMapping()) ? $client->getFieldsMapping() : null,
            'reactivateOnLogin' => $client->getReactivateOnLogin(),
            'createOnLogin' => $client->getCreateOnLogin(),
            'meta' => [
                'disabled' => $client->isDisabled(),
            ],
            'button' => [
                'displayed' => $client->isButtonDisplayed(),
                'icon' => $client->getButtonIcon(),
                'label' => $client->getButtonLabel(),
                'confirm' => $client->getButtonConfirm(),
            ],
            'organizations' => array_map(function (Organization $organization) {
                return $this->organizationSerializer->serialize($organization, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $client->getOrganizations()->toArray()),
        ];
    }

    public function deserialize(array $data, OAuthClient $client, ?array $options = []): OAuthClient
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $client);
        } else {
            $client->refreshUuid();
        }

        $this->sipe('serviceProvider', 'setServiceProvider', $data, $client);
        $this->sipe('name', 'setName', $data, $client);
        $this->sipe('clientId', 'setClientId', $data, $client);
        $this->sipe('clientSecret', 'setClientSecret', $data, $client);
        $this->sipe('urlAuthorize', 'setUrlAuthorize', $data, $client);
        $this->sipe('urlAccessToken', 'setUrlAccessToken', $data, $client);
        $this->sipe('urlResourceOwnerDetails', 'setUrlResourceOwnerDetails', $data, $client);
        $this->sipe('additionalParameters', 'setAdditionalParameters', $data, $client);
        $this->sipe('fieldsMapping', 'setFieldsMapping', $data, $client);
        $this->sipe('reactivateOnLogin', 'setReactivateOnLogin', $data, $client);
        $this->sipe('createOnLogin', 'setCreateOnLogin', $data, $client);
        $this->sipe('meta.disabled', 'setDisabled', $data, $client);
        $this->sipe('button.displayed', 'setButtonDisplayed', $data, $client);
        $this->sipe('button.icon', 'setButtonIcon', $data, $client);
        $this->sipe('button.label', 'setButtonLabel', $data, $client);
        $this->sipe('button.confirm', 'setButtonConfirm', $data, $client);

        if (array_key_exists('organizations', $data)) {
            $linkedOrganizations = $client->getOrganizations();

            $organizations = [];
            foreach ($data['organizations'] as $organizationData) {
                $organization = $this->om->getRepository(Organization::class)->findOneBy(['uuid' => $organizationData['id']]);
                if ($organization) {
                    $client->addOrganization($organization);
                    $organizations[] = $organization->getUuid();
                }
            }

            foreach ($linkedOrganizations as $linkedOrganization) {
                if (!in_array($linkedOrganization->getUuid(), $organizations)) {
                    $client->removeOrganization($linkedOrganization);
                }
            }
        }

        return $client;
    }
}
