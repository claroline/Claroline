<?php

namespace Claroline\CoreBundle\Manager;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Exception\ClarolineException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Entity\Role;

class UserManager
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var Doctrine\ORM\EntityRepository
     */
    private $userRepository;

    /**
     * @var Symfony\Component\Validator\Validator
     */
    private $validator;

    /**
     * @var Symfony\Component\Security\Core\Encoder\EncoderFactory
     */
    private $encoderFactory;

    /**
     * @var Claroline\CoreBundle\Manager\RoleManager
     */
    private $roleManager;

    public function __construct(
        EntityManager $em,
        Validator $validator,
        EncoderFactory $encoderFactory,
        RoleManager $roleManager
    )
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository('Claroline\CoreBundle\Entity\User');
        $this->validator = $validator;
        $this->encoderFactory = $encoderFactory;
        $this->roleManager = $roleManager;
    }

    public function hasUniqueUsername(User $user)
    {
        $sameUsers = $this->userRepository->findByUsername($user->getUsername());

        if (count($sameUsers) == 0)
        {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        $errors = $this->validator->validate($user);

        if (count($errors) > 0)
        {
            throw new ClarolineException(print_r($errors, true));
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
        $user->setPassword($password);

        $userRole = $this->roleManager->getRole('ROLE_USER', RoleManager::CREATE_IF_NOT_EXISTS);
        $user->addRole($userRole);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}