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

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;

class Updater021002
{
    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->setPublicUrlOnUsers();
    }

    protected function setPublicUrlOnUsers()
    {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        /** @var \Claroline\CoreBundle\Repository\UserRepository $userRepository */
        $userRepository = $entityManager->getRepository('ClarolineCoreBundle:User');
        /** @var \CLaroline\CoreBundle\Entity\User[] $users */
        $users = $userRepository->findByPublicUrl(null);

        /** @var \Claroline\CoreBundle\Manager\UserManager $userManager */
        $userManager = $this->container->get('claroline.manager.user_manager');

        foreach ($users as $key => $user) {
            $publicUrl = $userManager->generatePublicUrl($user);
            $user->setPublicUrl($publicUrl);
            $entityManager->persist($user);
            $entityManager->flush();
        }
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
