<?php

namespace Claroline\CoreBundle\Writer;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.writer.user_writer")
 */
class UserWriter
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function insertUser(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function deleteUser(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }

    public function createUser(
        $firstName,
        $lastName,
        $username,
        $pwd,
        $code,
        $email,
        $phone,
        AbstractWorkspace $workspace = null
    )
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username);
        $user->setPlainPassword($pwd);
        $user->setAdministrativeCode($code);
        $user->setMail($email);
        $user->setPhone($phone);
        $user->setPersonalWorkspace($workspace);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}