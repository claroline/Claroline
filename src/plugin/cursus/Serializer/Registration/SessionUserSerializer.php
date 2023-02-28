<?php

namespace Claroline\CursusBundle\Serializer\Registration;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CursusBundle\Entity\Registration\AbstractUserRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Serializer\SessionSerializer;

class SessionUserSerializer extends AbstractUserSerializer
{
    use SerializerTrait;

    private SessionSerializer $sessionSerializer;
    private FacetManager $facetManager;

    public function __construct(
        UserSerializer $userSerializer,
        SessionSerializer $sessionSerializer,
        FacetManager $facetManager
    ) {
        parent::__construct($userSerializer);

        $this->sessionSerializer = $sessionSerializer;
        $this->facetManager = $facetManager;
    }

    public function getClass()
    {
        return SessionUser::class;
    }

    /**
     * @param SessionUser $sessionUser
     */
    public function serialize(AbstractUserRegistration $sessionUser, array $options = []): array
    {
        $serialized = array_merge(parent::serialize($sessionUser, $options), [
            'session' => $this->sessionSerializer->serialize($sessionUser->getSession(), [Options::SERIALIZE_MINIMAL]),
        ]);

        $serialized['data'] = [];
        foreach ($sessionUser->getFacetValues() as $field) {
            // we just flatten field facets in the base user structure
            $serialized['data'][$field->getFieldFacet()->getUuid()] = $this->facetManager->serializeFieldValue(
                $sessionUser,
                $field->getType(),
                $field->getValue()
            );
        }

        return $serialized;
    }
}
