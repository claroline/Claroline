<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\InstallationBundle\Updater\Updater;

class Updater021000 extends Updater
{
    private $container;
    /** @var ObjectManager */
    private $om;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->updateDefaultPerms();
        $this->updateIcons();
    }

    public function updateDefaultPerms()
    {
        $tools = array(
            array('home', false, true),
            array('parameters', true, true),
            array('resource_manager', false, true),
            array('agenda', false, true),
            array('logs', false, true),
            array('analytics', false, true),
            array('users', false, true),
            array('badges', false, true),
        );

        $this->log('updating tools...');

        foreach ($tools as $tool) {
            $entity = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName($tool[0]);

            if ($entity) {
                $entity->setIsLockedForAdmin($tool[1]);
                $entity->setIsAnonymousExcluded($tool[2]);
                $this->om->persist($entity);
            }
        }

        $this->log('updating resource types...');
        $resourceTypes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        foreach ($resourceTypes as $resourceType) {
            $resourceType->setDefaultMask(1);
            $this->om->persist($resourceType);
        }

        $this->om->flush();
        $this->log('updating manager roles...');
        $this->log('this may take a while...');
        $managerRoles = $this->om->getRepository('ClarolineCoreBundle:Role')->searchByName('ROLE_WS_MANAGER');

        foreach ($managerRoles as $role) {
            $this->conn->query(
                "DELETE FROM claro_ordered_tool_role
                WHERE role_id = {$role->getId()}"
            );
        }

        $this->log('updating resource rights...');

        $this->log('removing administrator rights...');

        $roleAdmin = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN');

        $adminRights = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceRights')->findBy(
            array('role' => $roleAdmin)
        );

        foreach ($adminRights as $adminRight) {
            $this->om->remove($adminRight);
        }

        $this->om->flush();
        $this->log('adding user rights... ');
        $this->log('it may take a while...');

        $resourceNodes = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findAll();
        $roleUser = $this->om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');

        $this->om->startFlushSuite();
        $i = 0;

        foreach ($resourceNodes as $resourceNode) {
            $rightsManager = $this->container->get('claroline.manager.rights_manager');

            $rightsManager->create(0, $roleUser, $resourceNode, false);
            ++$i;

            if ($i % 200 === 0) {
                $this->om->endFlushSuite();
                $this->om->startFlushSuite();
            }
        }

        $this->om->endFlushSuite();
    }

    public function updateIcons()
    {
        $this->log('updating icons...');
        $coreIconWebDirRelativePath = 'bundles/clarolinecore/images/resources/icons/';
        $resourceImages = array(
            array('res_vector.png', 'application/postscript'),
            array('res_vector.png', 'image/svg+xml'),
            array('res_zip.png', 'application/zip'),
            array('res_zip.png', 'application/x-rar-compressed'),
            array('res_archive.png', 'application/x-gtar'),
            array('res_archive.png', 'application/x-7z-compressed'),
        );

        foreach ($resourceImages as $resourceImage) {
            $icon = new ResourceIcon();
            $icon->setRelativeUrl($coreIconWebDirRelativePath.$resourceImage[0]);
            $icon->setMimeType($resourceImage[1]);
            $icon->setShortcut(false);
            $this->om->persist($icon);

            $this->container->get('claroline.manager.icon_manager')->createShortcutIcon($icon);
        }
    }
}
