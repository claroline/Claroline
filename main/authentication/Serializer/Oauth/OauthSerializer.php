<?php

namespace Claroline\AuthenticationBundle\Serializer\Oauth;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.oauth")
 * @DI\Tag("claroline.serializer")
 */
class OauthSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return 'Claroline\AuthenticationBundle\Entity\Oauth\OauthUser';
    }
}
