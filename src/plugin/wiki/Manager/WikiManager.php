<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 3/11/15
 */

namespace Icap\WikiBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Repository\SectionRepository;
use Icap\WikiBundle\Serializer\WikiSerializer;

class WikiManager
{
    private SectionRepository $sectionRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly WikiSerializer $wikiSerializer
    ) {
        $this->sectionRepository = $this->om->getRepository(Section::class);
    }

    public function updateWiki(Wiki $wiki, $data): void
    {
        $this->wikiSerializer->deserialize($data, $wiki);
        $this->om->persist($wiki);
        $this->om->flush();
    }

    public function serializeWiki(Wiki $wiki): array
    {
        return $this->wikiSerializer->serialize($wiki);
    }

    public function copyWiki(Wiki $orgWiki, Wiki $newWiki, $loggedUser): Wiki
    {
        $orgRoot = $orgWiki->getRoot();

        $sections = $this->sectionRepository->children($orgRoot);
        array_unshift($sections, $orgRoot);
        $newSectionsMap = [];

        $newWiki->setWikiCreator($loggedUser);

        foreach ($sections as $section) {
            $newSection = new Section();
            $newSection->setWiki($newWiki);
            $newSection->setVisible($section->getVisible());
            $newSection->setAuthor($loggedUser);

            $activeContribution = new Contribution();
            $activeContribution->setTitle($section->getActiveContribution()->getTitle());
            $activeContribution->setText($section->getActiveContribution()->getText());
            $activeContribution->setSection($newSection);
            $activeContribution->setContributor($loggedUser);
            $newSection->setActiveContribution($activeContribution);

            if ($section->isRoot()) {
                $newWiki->setRoot($newSection);
                $this->om->persist($newWiki);
                $this->sectionRepository->persistAsFirstChild($newSection);
            } else {
                $newSectionParent = $newSectionsMap[$section->getParent()->getId()];
                $newSection->setParent($newSectionParent);
                $this->sectionRepository->persistAsLastChildOf($newSection, $newSectionParent);
            }
            $this->om->persist($activeContribution);

            $newSectionsMap[$section->getId()] = $newSection;
        }

        return $newWiki;
    }
}
