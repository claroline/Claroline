<?php

namespace Claroline\AuthenticationBundle\Manager;

use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\AppBundle\Persistence\ObjectManager;

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
