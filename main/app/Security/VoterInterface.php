<?php

namespace Claroline\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Claroline VoterInterface. This is used by the AbstractVoter and contains several utilities
 * methods to handle collections.
 */
interface VoterInterface
{
    /**
     * @param TokenInterface $token
     * @param mixed          $object
     * @param array          $attributes
     * @param array          $options
     *
     * @return int
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options);

    /**
     * @return string
     */
    public function getClass();

    /**
     * @return array
     */
    public function getSupportedActions();
}
