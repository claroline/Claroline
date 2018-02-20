<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/12/17
 * Time: 3:30 PM.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater110200 extends Updater
{
    const BATCH_SIZE = 500;

    private $container;
    protected $logger;
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
        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();
        $count = count($users);
        $i = 0;

        $this->log('Setting user main organization...');

        foreach ($users as $user) {
            ++$i;

            $administratedOrganizations = $user->getAdministratedOrganizations()->toArray();

            if (!$user->getMainOrganization()) {
                $this->log("Set {$user->getUsername()} main organization {$i}/{$count}");

                if (count($administratedOrganizations) > 0) {
                    $user->setMainOrganization($administratedOrganizations[0]);
                } else {
                    $organizations = $user->getOrganizations();
                    if (count($organizations) > 0) {
                        $user->setMainOrganization($organizations[0]);
                    } else {
                        $default = $this->container->get('claroline.manager.organization.organization_manager')->getDefault();
                        $user->setMainOrganization($default);
                    }
                }

                $this->om->persist($user);
            }

            if (0 === $i % self::BATCH_SIZE) {
                $this->log('Flushing...');
                $this->om->flush();
            }
        }

        $this->log('Flushing...');
        $this->om->flush();
    }

    public function createRoleAdminOrga()
    {
        $this->log('Create role admin organization...');

        $roleManager = $this->container->get('claroline.manager.role_manager');
        $role = $roleManager->createBaseRole('ROLE_ADMIN_ORGANIZATION', 'admin_organization');
        $workspacemanagement = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('workspace_management');
        $usermanagement = $manager->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('user_management');
        $usermanagement->addRole($role);
        $workspacemanagement->addRole($role);
        $this->om->persist($usermanagement);
        $this->om->persist($workspacemanagement);
        $this->om->flush();
    }
}
