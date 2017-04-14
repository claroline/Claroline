<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater100000 extends Updater
{
    private $organizationManager;
    private $om;

    public function __construct(ContainerInterface $container)
    {
        $this->organizationManager = $container->get('claroline.manager.organization.organization_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->addDefaultOrganizationToCursusAndCourses();
    }

    private function addDefaultOrganizationToCursusAndCourses()
    {
        $defaultOrganization = $this->organizationManager->getDefault(true);

        $this->log('Associating default organization to cursus...');
        $cursusRepo = $this->om->getRepository('Claroline\CursusBundle\Entity\Cursus');
        $allCursus = $cursusRepo->findAll();

        foreach ($allCursus as $cursus) {
            if (count($cursus->getOrganizations()) === 0) {
                $cursus->addOrganization($defaultOrganization);
                $this->om->persist($cursus);
            }
        }
        $this->log('Associating default organization to courses...');
        $courseRepo = $this->om->getRepository('Claroline\CursusBundle\Entity\Course');
        $allCourses = $courseRepo->findAll();

        foreach ($allCourses as $course) {
            if (count($course->getOrganizations()) === 0) {
                $course->addOrganization($defaultOrganization);
                $this->om->persist($course);
            }
        }
        $this->om->flush();
    }
}
