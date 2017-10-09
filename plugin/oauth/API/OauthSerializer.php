<?php

namespace Icap\OAuthBundle\API;

use Claroline\CoreBundle\API\Serializer\AbstractSerializer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.oauth")
 * @DI\Tag("claroline.serializer")
 */
class OauthSerializer extends AbstractSerializer
{
    public function getClass()
    {
        return 'Icap\OAuthBundle\Entity\OauthUser';
    }
}
