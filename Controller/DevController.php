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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Workspace\Configuration;

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
    public function ReinstallAction()
    {
        $kernel = $this->getContainer()->get('kernel');
        $start = new \DateTime();
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger(
            $this->getContainer()->get('doctrine.orm.entity_manager')
        );
        $purger->purge();

        //load the required fixture
        $fixture = new \Claroline\CoreBundle\DataFixtures\Required\LoadRequiredFixturesData();
        $referenceRepo = new ReferenceRepository($om);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->getContainer());
        $fixture->load($om);
        $om->startFlushSuite();

        //reset default template
        $defaultTemplatePath = $this->getContainer()->getParameter('kernel.root_dir') . '/../templates/default.zip';
        \Claroline\CoreBundle\Library\Workspace\TemplateBuilder::buildDefault($defaultTemplatePath);

        //install the plugins fixtures
        $bundles = $kernel->getBundles();
        $installer = $this->getContainer()->get('claroline.plugin.installer');

        foreach ($bundles as $bundle) {
            if ($bundle instanceof \Claroline\CoreBundle\Library\PluginBundle) {
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
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $user = new User();
        $user->setUsername($username);
        $user->setPlainPassword($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setMail($username . '@claroline.net');
        $userManager->createUserWithRole($user, $role);

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

    private function getContainer()
    {
        return $this->container;
    }
}