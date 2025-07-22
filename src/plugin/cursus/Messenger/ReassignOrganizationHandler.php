<?php

namespace Claroline\CursusBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Messenger\Message\ReassignOrganization;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Assign courses with no organization to the default one.
 */
#[AsMessageHandler]
class ReassignOrganizationHandler
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function __invoke(ReassignOrganization $reassignOrganization): void
    {
        $organization = $this->om->getRepository(Organization::class)->find($reassignOrganization->getOrganizationId());
        $courses = $this->om->getRepository(Course::class)->findWithNoOrganization();

        foreach ($courses as $course) {
            $course->addOrganization($organization);
            $this->om->persist($course);
        }

        $this->om->flush();
    }
}
