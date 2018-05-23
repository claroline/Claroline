<?php

namespace Icap\WikiBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Repository\ContributionRepository;
use Icap\WikiBundle\Repository\SectionRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.wiki.section_manager")
 */
class SectionManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var \Icap\WikiBundle\Repository\SectionRepository */
    protected $sectionRepository;

    protected $contributionRepository;

    /**
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "sectionRepository"      = @DI\Inject("icap.wiki.section_repository"),
     *     "contributionRepository" = @DI\Inject("icap.wiki.contribution_repository")
     * })
     *
     * @param ObjectManager          $om
     * @param SectionRepository      $sectionRepository
     * @param ContributionRepository $contributionRepository
     */
    public function __construct(ObjectManager $om, SectionRepository $sectionRepository, ContributionRepository $contributionRepository)
    {
        $this->om = $om;
        $this->sectionRepository = $sectionRepository;
        $this->contributionRepository = $contributionRepository;
    }

    /**
     * @return \Icap\WikiBundle\Repository\SectionRepository
     */
    public function getSectionRepository()
    {
        return $this->sectionRepository;
    }

    public function getArchivedSectionsForPosition(Section $section)
    {
        $sections = $this->getSectionRepository()->findSectionsForPosition($section);
        $archivedSections = [];
        $prefixesArray = [];
        $childrens = [];
        foreach ($sections as $simpleSection) {
            if (isset($childrens[$simpleSection['parentId']])) {
                $childrens[$simpleSection['parentId']] += 1;
            } else {
                $childrens[$simpleSection['parentId']] = 1;
            }
            if (isset($prefixesArray[$simpleSection['parentId']])) {
                $prefix = $prefixesArray[$simpleSection['parentId']].$childrens[$simpleSection['parentId']];
            } else {
                $prefix = $childrens[$simpleSection['parentId']];
            }
            $archivedSections[$simpleSection['id']] = $prefix.' '.$simpleSection['title'];
            $prefixesArray[$simpleSection['id']] = '&nbsp;'.$prefix.'.';
        }

        return $archivedSections;
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
        $sections = $this->sectionRepository->findByAuthor($from);

        if (count($sections) > 0) {
            foreach ($sections as $section) {
                $section->setAuthor($to);
            }

            $this->om->flush();
        }

        return count($sections);
    }
}
