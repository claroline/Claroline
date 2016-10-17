<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes courses from the platform.
 */
class RemoveCoursesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:courses:delete')
            ->setDescription('Deletes courses that are not associated to a cursus. Sessions and associated workspaces are also deleted');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $cursusManager = $this->getContainer()->get('claroline.manager.cursus_manager');
        $coursesToDelete = $cursusManager->getIndependentCourses();
        $om->startFlushSuite();
        $i = 1;

        foreach ($coursesToDelete as $course) {
            $sessionsToDelete = $course->getSessions();

            foreach ($sessionsToDelete as $session) {
                $sessionName = $session->getName();
                $output->writeln("<info> Deleting training session [$sessionName]... </info>");
                $cursusManager->deleteCourseSession($session, true);

                if ($i % 100 === 0) {
                    $om->forceFlush();
                }
                ++$i;
            }
            $courseTitle = $course->getTitle();
            $output->writeln("<info> Deleting training [$courseTitle]... </info>");
            $cursusManager->deleteCourse($course);

            if ($i % 100 === 0) {
                $om->forceFlush();
            }
            ++$i;
        }
        $om->endFlushSuite();
    }
}
