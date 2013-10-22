<?php

namespace Icap\WikiBundle\Manager;

use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Repository\SectionRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.wiki.section_manager")
 */
class SectionManager
{
    /** @var \Icap\WikiBundle\Repository\SectionRepository */
    protected $sectionRepository;

    /**
     * @DI\InjectParams({
     *     "sectionRepository" = @DI\Inject("icap.wiki.section_repository")
     * })
     */
    public function __construct(SectionRepository $sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
    }


    /**
     * @return \Icap\BlogBundle\Repository\TagRepository
     */
    public function getSectionRepository ()
    {
        return $this->sectionRepository;
    }


    /**
     *
     */
    public function getArchivedSectionsForPosition (Section $section)
    {
        $sections = $this->getSectionRepository()->findSectionsForPosition($section);
        $archivedSections = array();
        $prefixesArray = array();
        $childrens = array();
        foreach ($sections as $simpleSection){
            if ($childrens[$simpleSection['parentId']] !== null) {
                $childrens[$simpleSection['parentId']] += 1;
            }
            else {
                $childrens[$simpleSection['parentId']] = 1;
            }
            $prefix = $prefixesArray[$simpleSection['parentId']].$childrens[$simpleSection['parentId']];
            $archivedSections[$simpleSection['id']] = $prefix." ".$simpleSection['title'];
            $prefixesArray[$simpleSection['id']] = $prefix.".";
        }

        return $archivedSections;
    }

}