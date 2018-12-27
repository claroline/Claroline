<?php

namespace Claroline\AuthenticationBundle\Serializer\Oauth;

use Claroline\AppBundle\API\Serializer\GenericSerializer;
use Claroline\AuthenticationBundle\Entity\Oauth\OauthUser;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.oauth")
 * @DI\Tag("claroline.serializer")
 */
class OauthSerializer extends GenericSerializer
{
    public function getClass()
    {
        return OauthUser::class;
    }
}
