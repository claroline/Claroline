<?php

namespace Claroline\CoreBundle\Library\Security\Acl;

use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

final class ClassIdentity implements ObjectIdentityInterface
{
    private $classFQCN;

    public function __construct($classFQCN)
    {
        if (empty($classFQCN)) {
            throw new \InvalidArgumentException('$classFQCN cannot be empty.');
        }

        if (!class_exists($classFQCN)) {
            throw new \InvalidArgumentException(
                "Class {$classFQCN} doesn't exist or couldn't be loaded ."
            );
        }

        $this->classFQCN = $classFQCN;
    }

    static public function fromDomainClass($classFQCN)
    {
        return new self($classFQCN);
    }

    public function getIdentifier()
    {
        return $this->classFQCN;
    }

    public function getType()
    {
        return $this->classFQCN;
    }

    public function equals(ObjectIdentityInterface $identity)
    {
        return $this->identifier === $identity->getIdentifier()
            && $this->type === $identity->getType();
    }
}