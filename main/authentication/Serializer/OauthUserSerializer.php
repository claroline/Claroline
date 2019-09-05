<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\GenericSerializer;
use Claroline\AuthenticationBundle\Entity\OauthUser;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.oauth_user")
 * @DI\Tag("claroline.serializer")
 */
class OauthUserSerializer extends GenericSerializer
{
    public function getClass()
    {
        return OauthUser::class;
    }
}
