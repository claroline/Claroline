<?php

namespace Claroline\UserBundle\Service\UserManager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Claroline\UserBundle\Entity\User;
use Claroline\UserBundle\Service\UserManager\Exception\UserException;

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
     * @var Symfony\Component\Security\Core\Encoder\EncoderFactory
     */
    private $factory;

    public function __construct(EntityManager $em, EncoderFactory $factory)
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository('Claroline\UserBundle\Entity\User');
        $this->factory = $factory;
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
        if (! $this->hasUniqueUsername($user))
        {
            throw new UserException("Username '{$user->getUsername()}' is already registered.");
        }

        $encoder = $this->factory->getEncoder($user);
        $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
        $user->setPassword($password);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}