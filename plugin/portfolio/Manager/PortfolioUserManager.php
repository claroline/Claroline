<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Repository\PortfolioUserRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_portfolio.manager.portfolio_user")
 */
class PortfolioUserManager
{
    /** @var ObjectManager */
    private $om;

    /** @var PortfolioUserRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('IcapPortfolioBundle:PortfolioUser');
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
        $portfolioUsers = $this->repo->findByUser($from);

        if (count($portfolioUsers) > 0) {
            foreach ($portfolioUsers as $portfolioUser) {
                $portfolioUser->setUser($to);
            }

            $this->om->flush();
        }

        return count($portfolioUsers);
    }
}
