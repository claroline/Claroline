<?php

namespace Claroline\SecurityBundle\Service;

use Symfony\Component\Validator\Validator;
use Doctrine\ORM\EntityManager;
use Claroline\SecurityBundle\Entity\Role;
use Claroline\SecurityBundle\Service\Exception\RoleException;

class RoleManager
{
    const CREATE_IF_NOT_EXISTS = true;
    
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Symfony\Component\Validator\Validator
     */
    private $validator;

    /**
     * @var Claroline\SecurityBundle\Repository\RoleRepository
     */
    private $roleRepository;

    public function __construct(EntityManager $em, Validator $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->roleRepository = $em->getRepository('Claroline\SecurityBundle\Entity\Role');
    }

    public function getRole($roleName, $createIfNotExists = false)
    {
        $role = $this->roleRepository->findOneByName($roleName);

        if ($role === null && $createIfNotExists === true)
        {
            $role = new Role();
            $role->setName($roleName);
            $this->create($role);
        }

        return $role;
    }

    public function create(Role $role)
    {
        $errors = $this->validator->validate($role);

        if (count($errors) > 0)
        {
            throw new RoleException(print_r($errors, true));
        }

        $this->em->persist($role);
        $this->em->flush();
    }
}