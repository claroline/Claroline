<?php

namespace Claroline\AuthenticationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;

class AuthenticationManager
{
    private ObjectManager $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getParameters(): AuthenticationParameters
    {
        return $this->om->getRepository(AuthenticationParameters::class)->findOneBy([], ['id' => 'DESC']);
    }
}
