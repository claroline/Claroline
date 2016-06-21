<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\User;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratePublicUrlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('claroline:user:generate-public-url')
            ->setDescription('Generate public url for user that don\'t have one.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Updating public url for users...');

        $objectManager = $this->getContainer()->get('claroline.persistence.object_manager');

        /** @var \Claroline\CoreBundle\Repository\UserRepository $userRepository */
        $userRepository = $objectManager->getRepository('ClarolineCoreBundle:User');

        $output->writeln('Start of update, it may take a while to process - '.date('Y/m/d H:i:s'));

        /** @var \Claroline\CoreBundle\Manager\UserManager $userManager */
        $userManager = $this->getContainer()->get('claroline.manager.user_manager');
        $nbUsers = 0;

        /** @var \Claroline\CoreBundle\Entity\User $user */
        $user = $userRepository->findOneByPublicUrl(null);
        while (null !== $user) {
            $publicUrl = $userManager->generatePublicUrl($user);

            $user->setPublicUrl($publicUrl);
            $objectManager->persist($user);
            $objectManager->flush();

            ++$nbUsers;
            if (100 === $nbUsers) {
                $output->writeln('    '.$nbUsers.' updated users - '.date('Y/m/d H:i:s'));
                $nbUsers = 0;
            }

            $personalWorkspace = $user->getPersonalWorkspace();
            if (null !== $personalWorkspace) {
                $objectManager->detach($personalWorkspace);
            }
            $objectManager->detach($user);
            $user = $userRepository->findOneByPublicUrl(null);
        }

        $output->writeln('Public url for users updated.');
    }
}
