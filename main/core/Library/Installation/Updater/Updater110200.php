<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/12/17
 * Time: 3:30 PM.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater110200 extends Updater
{
    const BATCH_SIZE = 500;

    private $container;
    protected $logger;
    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->setMainOrganizations();
        $this->createRoleAdminOrga();
    }

    public function setMainOrganizations()
    {
        $this->log('Setting user main organization...');
        $total = intval($this->om
            ->getRepository('ClarolineCoreBundle:User')
            ->findUsersWithoutMainOrganization(true));
        $i = 0;
        while ($i < $total) {
            $defaultOrganization = $this->container->get('claroline.manager.organization.organization_manager')->getDefault();
            $users = $this->om
                ->getRepository('ClarolineCoreBundle:User')
                ->findUsersWithoutMainOrganization(false, self::BATCH_SIZE, 0);
            foreach ($users as $user) {
                ++$i;

                $administratedOrganizations = $user->getAdministratedOrganizations()->toArray();

                if (!$user->getMainOrganization()) {
                    $this->log("Set {$user->getUsername()} main organization {$i}/{$total}");

                    if (count($administratedOrganizations) > 0) {
                        $user->setMainOrganization($administratedOrganizations[0]);
                    } else {
                        $organizations = $user->getOrganizations();
                        if (count($organizations) > 0) {
                            $user->setMainOrganization($organizations[0]);
                        } else {
                            $user->setMainOrganization($defaultOrganization);
                        }
                    }

                    $this->om->persist($user);
                }
            }

            $this->log('Flushing...');
            $this->om->flush();
            $this->om->clear();
        }
    }

    public function createRoleAdminOrga()
    {
        $this->log('Create role admin organization...');

        $roleManager = $this->container->get('claroline.manager.role_manager');

        if (!$this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN_ORGANIZATION')) {
            $role = $roleManager->createBaseRole('ROLE_ADMIN_ORGANIZATION', 'admin_organization');

            $workspacemanagement = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('workspace_management');
            $usermanagement = $this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('user_management');
            $usermanagement->addRole($role);
            $workspacemanagement->addRole($role);
            $this->om->persist($usermanagement);
            $this->om->persist($workspacemanagement);
            $this->om->flush();
        }

        $this->log('Role admin organization created!');
    }
}
