<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PresenceBundle\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use FormaLibre\PresenceBundle\Entity\SchoolYear;
use FormaLibre\PresenceBundle\Entity\Status;

class LoadPresencesData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function load(ObjectManager $manager)
    {
        $actualdate = date('m', strtotime('now'));
        if ($actualdate >= 9) {
            $beginDate = '01-09-'.date('Y');
            $endDate = '30-06-'.date('Y', strtotime('+1 year'));
            $schoolYearName = date('Y').'-'.date('Y', strtotime('+1 year'));
        } else {
            $beginDate = '01-09-'.date('Y', strtotime('-1 year'));
            $endDate = '30-06-'.date('Y');
            $schoolYearName = date('Y', strtotime('-1 year')).'-'.date('Y');
        }

        $beginDateFormat = \DateTime::createFromFormat('d-m-Y', $beginDate);
        $endDateFormat = \DateTime::createFromFormat('d-m-Y', $endDate);
        $beginHourFormat = \DateTime::createFromFormat('H:i', '07:00');
        $endHourFormat = \DateTime::createFromFormat('H:i', '17:00');

        $demoSchoolYear = new SchoolYear();
        $demoSchoolYear->setSchoolYearName($schoolYearName);
        $demoSchoolYear->setSchoolYearBegin($beginDateFormat);
        $demoSchoolYear->setSchoolYearEnd($endDateFormat);
        $demoSchoolYear->setSchoolDayBeginHour($beginHourFormat);
        $demoSchoolYear->setSchoolDayendHour($endHourFormat);
        $demoSchoolYear->setSchoolYearActual(true);
        $manager->persist($demoSchoolYear);

        $demoStatus = new Status();
        $demoStatus->setStatusByDefault(true);
        $demoStatus->setStatusColor('#0fef40');
        $demoStatus->setStatusName('Présent');
        $manager->persist($demoStatus);

        $demoStatus2 = new Status();
        $demoStatus2->setStatusByDefault(true);
        $demoStatus2->setStatusColor('#f20000');
        $demoStatus2->setStatusName('Absent');
        $manager->persist($demoStatus2);

        $demoStatus3 = new Status();
        $demoStatus3->setStatusByDefault(true);
        $demoStatus3->setStatusColor('#ffb31f');
        $demoStatus3->setStatusName('Retard');
        $manager->persist($demoStatus3);

        $manager->flush();
    }
}
