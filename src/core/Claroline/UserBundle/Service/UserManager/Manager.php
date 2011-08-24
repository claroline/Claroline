<?php

namespace Claroline\UserBundle\Service\UserManager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Claroline\UserBundle\Entity\User;
use Claroline\UserBundle\Service\UserManager\Exception\UserException;
use Claroline\SecurityBundle\Service\RoleManager;

class Manager
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
     * @var Claroline\SecurityBundle\Service\RoleManager
     */
    private $roleManager;

    public function __construct(EntityManager $em,
                                Validator $validator,
                                EncoderFactory $encoderFactory,
                                RoleManager $roleManager)
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository('Claroline\UserBundle\Entity\User');
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
            throw new UserException(print_r($errors, true));
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
        $user->setPassword($password);

        $user->addRole($this->roleManager->getRole('ROLE_USER'));

        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}