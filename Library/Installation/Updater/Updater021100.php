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


class Updater021100
{
    private $container;
    private $logger;
    private $objectManager;
    private $conn;

    public function __construct($container)
    {
        $this->container = $container;
        $this->objectManager = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
    }

    public function postUpdate()
    {
        $this->log('Updating default mails layout...');
        $repository = $this->objectManager->getRepository('Claroline\CoreBundle\Entity\ContentTranslation');

        $frLayout = '<div></div>%content%<div></hr><p>Ce mail vous a été envoyé par %first_name% %last_name%</p>';
        $frLayout .= '<p>Powered by %platform_name%</p></div>';
        $enLayout = '<div></div>%content%<div></hr><p>This mail was sent to you by %first_name% %last_name%</p>';
        $enLayout .= '<p>Powered by %platform_name%</p></div>';

        $layout = $this->objectManager->getRepository('ClarolineCoreBundle:Content')->findOneByType('claro_mail_layout');
        $layout->setType('claro_mail_layout');
        $layout->setContent($enLayout);
        $repository->translate($layout, 'content', 'fr', $frLayout);
        $this->objectManager->persist($layout);

        $this->objectManager->flush();

        $this->setPublicUrlOnUsers();
    }

    protected function setPublicUrlOnUsers()
    {
        $this->log('Updating public url for users...');

        /** @var \Claroline\CoreBundle\Repository\UserRepository $userRepository */
        $userRepository = $this->objectManager->getRepository('ClarolineCoreBundle:User');
        /** @var \CLaroline\CoreBundle\Entity\User[] $users */
        $users = $userRepository->findByPublicUrl(null);

        /** @var \Claroline\CoreBundle\Manager\UserManager $userManager */
        $userManager = $this->container->get('claroline.manager.user_manager');

        foreach ($users as $key => $user) {
            $publicUrl = $userManager->generatePublicUrl($user);
            $user->setPublicUrl($publicUrl);
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }
        $this->log('Public url for users updated.');
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