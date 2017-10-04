<?php

namespace Claroline\CoreBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface VoterInterface
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options);
    public function getSupportedActions();
    public function getClass();
}
