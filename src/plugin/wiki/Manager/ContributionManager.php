<?php

namespace Icap\WikiBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\HtmlDiff\HtmlDiff;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Repository\ContributionRepository;
use Icap\WikiBundle\Serializer\ContributionSerializer;

class ContributionManager
{
    private ContributionRepository $contributionRepository;

    public function __construct(
        ObjectManager $om,
        private readonly ContributionSerializer $contributionSerializer
    ) {
        $this->contributionRepository = $om->getRepository(Contribution::class);
    }

    public function serializeContribution(Contribution $contribution): array
    {
        return $this->contributionSerializer->serialize($contribution);
    }

    public function serializeContributions($contributions): array
    {
        $serialized = [];
        foreach ($contributions as $contribution) {
            $serialized[] = $this->serializeContribution($contribution);
        }

        return $serialized;
    }

    public function compareContributions(Section $section, array $uuids): array
    {
        $contributions = $this->contributionRepository->findyBySectionAndUuids($section, $uuids);
        $titleDiff = new HtmlDiff($contributions[0]->getTitle(), $contributions[1]->getTitle(), false);
        $textDiff = new HtmlDiff($contributions[0]->getText(), $contributions[1]->getText(), true);
        $contribution = new Contribution();
        $contribution->setText($textDiff->outputDiff()->toString());
        $contribution->setTitle($titleDiff->outputDiff()->toString());
        $contribution->setContributor($contributions[1]->getContributor());
        $contribution->setCreationDate($contributions[1]->getCreationDate());
        $contribution->refreshUuid();
        $contributions[1] = $contribution;

        return $contributions;
    }
}
