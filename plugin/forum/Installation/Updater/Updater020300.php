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

class Updater020300 extends Updater
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
        $this->log('Updating forum table...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $forums = $em->getRepository('ClarolineForumBundle:Forum')->findAll();

        foreach ($forums as $forum) {
            $categories = $forum->getCategories();

            foreach ($categories as $cat) {
                $subjects = $cat->getSubjects();

                foreach ($subjects as $subject) {
                    $subject->isClosed(false);
                }
            }
        }

        $em->flush();
    }
}
