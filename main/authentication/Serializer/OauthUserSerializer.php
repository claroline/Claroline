<?php

namespace Claroline\AuthenticationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\GenericSerializer;
use Claroline\AuthenticationBundle\Entity\OauthUser;

class OauthUserSerializer extends GenericSerializer
{
    public function getClass()
    {
        return OauthUser::class;
    }
}
