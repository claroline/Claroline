<?php

namespace Claroline\CoreBundle\DataFixtures\Demo;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    private $users;
    private $container;
    private $withWorkspace;

    /**
     * Constructor. Expects an associative array where each key are a firstname and
     * a lastname separated by a space and each value a role name (e.g. 'John Doe' => 'admin').
     * Roles must have been loaded and referenced in a previous fixtures with a 'role/[role name]' label.
     *
     * Users will be created with the following properties :
     *
     * Username = username
     * Password = username
     * First name = ucfirst(username)
     * Last name = Doe
     *
     * For each user, three fixture references will be added :
     * - 'user/[username]'      (user)
     * - 'workspace/[username]' (user's personal workspace)
     * - 'directory/[username]' (user's workspace resource directory)
     *
     * @param array $users
     */
    public function __construct(array $users, $withWorkspace = true)
    {
        $this->users = $users;
        $this->withWorkspace = $withWorkspace;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $userCreator = $this->container->get('claroline.manager.user_manager');
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $resourceRepo = $manager->getRepository('ClarolineCoreBundle:Resource\AbstractResource');

        foreach ($this->users as $names => $role) {
            $namesArray = explode(' ', $names);
            $firstName = $namesArray[0];
            $lastName = (isset($namesArray[1])) ? $namesArray[1]: '';
            $username = $firstName.ucfirst($lastName);
            $user = new User();
            $user->setAdministrativeCode('UCL-'.$username.'-'.rand(0, 1000));
            $user->setFirstName($firstName);
            $lastName = ($lastName == '') ? 'Doe': $lastName;
            $user->setLastName($lastName);
            $user->setUserName($username);
            $user->setPlainPassword($username);
            $roleManager->associateRole($user, $this->getReference("role/{$role}"));

            if ($this->withWorkspace) {
                $userCreator->createUser($user);
                $this->addReference("workspace/{$names}", $user->getPersonalWorkspace());
                $this->addReference(
                    "directory/{$names}",
                $resourceRepo->findWorkspaceRoot($user->getPersonalWorkspace())
            );
            } else {
                $userCreator->insertUser($user);
            }

            $this->addReference("user/{$names}", $user);

            $manager->flush();
        }
    }
}