<?php

namespace Icap\WikiBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\HtmlDiff\HtmlDiff;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Repository\ContributionRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.wiki.contribution_manager")
 */
class ContributionManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var \Icap\WikiBundle\Repository\ContributionRepository */
    protected $contributionRepository;

    /**
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "contributionRepository" = @DI\Inject("icap.wiki.contribution_repository")
     * })
     *
     * @param ObjectManager          $om
     * @param ContributionRepository $contributionRepository
     */
    public function __construct(ObjectManager $om, ContributionRepository $contributionRepository)
    {
        $this->om = $om;
        $this->contributionRepository = $contributionRepository;
    }

    /**
     * @return \Icap\WikiBundle\Repository\ContributionRepository
     */
    public function getContributionRepository()
    {
        return $this->contributionRepository;
    }

    /**
     * @param Section $section
     * @param array   $ids
     *
     * @return array $contributions
     */
    public function compareContributions(Section $section, $ids)
    {
        $contributions = $this->contributionRepository->findyBySectionAndIds($section, $ids);
        $titleDiff = new HtmlDiff($contributions[0]->getTitle(), $contributions[1]->getTitle(), false);
        $textDiff = new HtmlDiff($contributions[0]->getText(), $contributions[1]->getText(), true);
        $contribution = new Contribution();
        $contribution->setText($textDiff->outputDiff()->toString());
        $contribution->setTitle($titleDiff->outputDiff()->toString());
        $contribution->setContributor($contributions[1]->getContributor());
        $contribution->setCreationDate($contributions[1]->getCreationDate());
        $contributions[1] = $contribution;

        return $contributions;
    }

    public function getContributions(Section $section)
    {
        return $this->contributionRepository->findAllForSection($section);
    }

    public function getContribution(Contribution $contribution)
    {
        return $this->contributionRepository->findById($contribution->getId());
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
        $contributions = $this->contributionRepository->findByContributor($from);

        if (count($contributions) > 0) {
            foreach ($contributions as $contribution) {
                $contribution->setContributor($to);
            }

            $this->om->flush();
        }

        return count($contributions);
    }
}
