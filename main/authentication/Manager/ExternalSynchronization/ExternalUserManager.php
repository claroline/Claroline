<?php

namespace Claroline\AuthenticationBundle\Manager\ExternalSynchronization;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Repository\ExternalSynchronization\ExternalUserRepository;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ExternalUserManager.
 *
 * @DI\Service("claroline.manager.external_user_manager")
 */
class ExternalUserManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ExternalUserRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('ClarolineAuthenticationBundle:ExternalSynchronization\ExternalUser');
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $externalUsers = $this->repo->findByUser($from);

        if (count($externalUsers) > 0) {
            foreach ($externalUsers as $externalUser) {
                $externalUser->setUser($to);
            }

            $this->om->flush();
        }

        return count($externalUsers);
    }
}
