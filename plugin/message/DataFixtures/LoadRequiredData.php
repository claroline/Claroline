<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\DataFixtures;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The fuck is this ? Change that.
 */
class LoadRequiredData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $om)
    {
        $messagePlugin = $this->getPluginFromIdentityMapOrScheduledForInsert(
            $om,
            'Claroline',
            'MessageBundle'
        );

        if (!is_null($messagePlugin)) {
            $toolRepo = $om->getRepository('ClarolineCoreBundle:Tool\Tool');
            $messageTool = $toolRepo->findOneBy(['name' => 'message']);

            if (is_null($messageTool)) {
                $messageTool = new Tool();
                $messageTool->setName('message');
                $messageTool->setClass('envelope');
                $messageTool->setDisplayableInWorkspace(false);
                $messageTool->setDisplayableInDesktop(true);
                $messageTool->setPlugin($messagePlugin);
                $om->persist($messageTool);
                $adminMessageOt = new OrderedTool();
                $adminMessageOt->setName('message');
                $adminMessageOt->setTool($messageTool);
                $adminMessageOt->setLocked(false);
                $adminMessageOt->setOrder(1);
                $adminMessageOt->setType(0);
                $adminMessageOt->setVisibleInDesktop(true);
                $om->persist($adminMessageOt);
                $userRepo = $om->getRepository('ClarolineCoreBundle:User');
                $users = $userRepo->findAllEnabledUsers();

                foreach ($users as $user) {
                    $messageOt = new OrderedTool();
                    $messageOt->setName('message');
                    $messageOt->setTool($messageTool);
                    $messageOt->setUser($user);
                    $messageOt->setLocked(false);
                    $messageOt->setOrder(1);
                    $messageOt->setType(0);
                    $messageOt->setVisibleInDesktop(true);
                    $om->persist($messageOt);
                }
            } else {
                $messageTool->setPlugin($messagePlugin);
                $om->persist($messageTool);
            }
            $om->flush();
        }
    }

    private function getPluginFromIdentityMapOrScheduledForInsert(
        ObjectManager $om,
        $vendorName,
        $bundleName
    ) {
        $result = $this->getPluginFromIdentityMap($om, $vendorName, $bundleName);

        if (!is_null($result)) {
            return $result;
        } else {
            return $this->getPluginScheduledForInsert($om, $vendorName, $bundleName);
        }
    }

    private function getPluginFromIdentityMap(
        ObjectManager $om,
        $vendorName,
        $bundleName
    ) {
        $result = null;
        $map = $om->getUnitOfWork()->getIdentityMap();

        if (array_key_exists('Claroline\CoreBundle\Entity\Plugin', $map)) {
            foreach ($map['Claroline\CoreBundle\Entity\Plugin'] as $plugin) {
                if ($plugin->getVendorName() === $vendorName &&
                    $plugin->getBundleName() === $bundleName) {
                    $result = $plugin;
                    break;
                }
            }
        }

        return $result;
    }

    private function getPluginScheduledForInsert(
        ObjectManager $om,
        $vendorName,
        $bundleName
    ) {
        $result = null;
        $scheduledForInsert = $om->getUnitOfWork()->getScheduledEntityInsertions();

        foreach ($scheduledForInsert as $entity) {
            if ('Claroline\CoreBundle\Entity\Plugin' === get_class($entity) &&
                $entity->getVendorName() === $vendorName &&
                $entity->getBundleName() === $bundleName) {
                $result = $entity;
                break;
            }
        }

        return $result;
    }
}
