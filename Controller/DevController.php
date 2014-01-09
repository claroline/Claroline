<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\DataFixtures\Required\LoadRequiredFixturesData;

class DevController extends Controller
{
    /**
     * @EXT\Route(
     *     "/reinstall",
     *     name="claro_dev_reinstall",
     * )
     * @EXT\Method("GET")
     *
     * @return Response
     */
    public function reinstallAction()
    {
        $kernel = $this->container->get('kernel');
        $start = new \DateTime();
        $om = $this->container->get('claroline.persistence.object_manager');
        $purger = new ORMPurger(
            $this->container->get('doctrine.orm.entity_manager')
        );
        $purger->purge();

        //load the required fixture
        $fixture = new LoadRequiredFixturesData();
        $referenceRepo = new ReferenceRepository($om);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->container);
        $fixture->load($om);
        $om->startFlushSuite();

        //reset default template
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        TemplateBuilder::buildDefault($defaultTemplatePath);

        //install the plugins fixtures
        $bundles = $kernel->getBundles();
        $installer = $this->container->get('claroline.plugin.installer');

        foreach ($bundles as $bundle) {
            if ($bundle instanceof PluginBundle) {
                //install the bundle !
                $installer->install($bundle);
            }
        }

        $om->endFlushSuite();
        $end = new \DateTime();
        $diff = $start->diff($end);
        $duration = $diff->i > 0 ? $diff->i . 'm ' : '';
        $duration .= $diff->s . 's';

        return new Response('duration :' . $duration);
    }

    /**
     * @EXT\Route(
     *     "/user/create/{username}/{role}",
     *     name="claro_dev_create_user",
     * )
     * @EXT\Method("GET")
     *
     * @param $username
     * @param $role
     *
     * @return Response
     */
    public function createUser($username, $role)
    {
        $userManager = $this->container->get('claroline.manager.user_manager');
        $user = new User();
        $user->setUsername($username);
        $user->setPlainPassword($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setMail($username . '@claroline.net');
        $user->setLocale('en');
        $userManager->createUserWithRole($user, $role);

        return new Response('done');
    }

    /**
     * @EXT\Route(
     *     "/group/create/{name}",
     *     name="claro_dev_create_group",
     * )
     * @EXT\Method("GET")
     *
     * @param $name
     * @return Response
     */
    public function createGroup($name)
    {
        $groupManager = $this->container->get('claroline.manager.group_manager');
        $group = new Group();
        $group->setName($name);
        $role = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Role')
            ->findOneByName('ROLE_USER');
        $group->addRole($role);
        $groupManager->insertGroup($group);

        return new Response('done');
    }

    /**
     * @EXT\Route(
     *     "/workspace/create/{workspaceName}/{username}"
     * )
     * @EXT\Method("GET")
     *
     * @param $workspaceName
     * @param $username
     *
     * @return Response
     */
    public function createWorkspace($workspaceName, $username)
    {
        $ds = DIRECTORY_SEPARATOR;
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $template = $this->container
            ->getParameter('claroline.param.templates_directory') . $ds . 'default.zip';
        $config = new Configuration($template);
        $config->setWorkspaceName($workspaceName);
        $config->setWorkspaceCode($workspaceName);
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ClarolineCoreBundle:User')->findOneByUsername($username);
        $workspaceManager->create($config, $user);

        return new Response('done');
    }
}