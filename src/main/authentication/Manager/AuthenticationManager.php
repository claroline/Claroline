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
        $parameters = $this->om->getRepository(AuthenticationParameters::class)->findOneBy([], ['id' => 'DESC']);

        if (null === $parameters) {
            $parameters = new AuthenticationParameters();
        }

        return $parameters;
    }

    public function updateParameters(AuthenticationParameters $parameters): void
    {
        $this->om->persist($parameters);
        $this->om->flush();
    }
}
