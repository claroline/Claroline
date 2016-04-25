<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater020210 extends Updater
{
    private $container;
    /** @var  Connection */
    private $conn;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Updating default mask...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $forumType = $repo->findOneByName('claroline_forum');
        $forumType->setDefaultMask(33);
        $em->persist($forumType);
        $em->flush();
        $this->log('Updating forum plugin...');
        $plugin = $em->getRepository('Claroline\CoreBundle\Entity\Plugin')->findOneByBundleName('ForumBundle');
        $plugin->setHasOptions(false);
        $em->persist($forumType);
        $em->flush();
    }
}
