<?php

namespace Claroline\AppBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface as BaseVoterInterface;

interface VoterInterface extends BaseVoterInterface
{
    /** @var string */
    const CREATE = 'CREATE';
    /** @var string */
    const EDIT = 'EDIT';
    const COPY = 'COPY';
    /** @var string */
    const ADMINISTRATE = 'ADMINISTRATE';
    /** @var string */
    const DELETE = 'DELETE';
    /** @var string */
    const VIEW = 'VIEW';
    /** @var string */
    const OPEN = 'OPEN';
    /** @var string */
    const PATCH = 'PATCH';

    /**
     * @param mixed $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int;

    /**
     * @return string
     */
    public function getClass();

    public function getSupportedActions(): ?array;
}
