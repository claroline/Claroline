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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.wiki.manager")
 */
class WikiManager
{
    /**
     * @var \Claroline\AppBundle\Persistence\ObjectManager
     */
    private $om;

    /**
     * @var \Icap\WikiBundle\Repository\SectionRepository
     */
    private $sectionRepository;

    /**
     * @var \Icap\WikiBundle\Repository\ContributionRepository
     */
    private $contributionRepository;

    /**
     * @var \Claroline\CoreBundle\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->sectionRepository = $this->om->getRepository('IcapWikiBundle:Section');
        $this->contributionRepository = $this->om->getRepository('IcapWikiBundle:Contribution');
        $this->userRepository = $this->om->getRepository('ClarolineCoreBundle:User');
    }

    public function copyWiki(Wiki $orgWiki, $loggedUser)
    {
        $orgRoot = $orgWiki->getRoot();

        $sections = $this->sectionRepository->children($orgRoot);
        array_unshift($sections, $orgRoot);
        $newSectionsMap = [];

        $newWiki = new Wiki();
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

    /**
     * Imports wiki object from array
     * (see WikiImporter for structure and description).
     *
     * @param array $data
     * @param $rootPath
     * @param $loggedUser
     *
     * @return Wiki
     */
    public function importWiki(array $data, $rootPath, $loggedUser)
    {
        $wiki = new Wiki();
        if (isset($data['data'])) {
            $wikiData = $data['data'];
            $wiki->setMode($wikiData['options']['mode']);
            $wiki->setDisplaySectionNumbers($wikiData['options']['display_section_numbers']);
            $sectionsMap = [];
            foreach ($wikiData['sections'] as $section) {
                $entitySection = new Section();
                $entitySection->setWiki($wiki);
                $entitySection->setDeleted($section['deleted']);
                $entitySection->setDeletionDate((null !== $section['deletion_date']) ? new \DateTime('@'.$section['deletion_date']) : null);
                $entitySection->setCreationDate((null !== $section['creation_date']) ? new \DateTime('@'.$section['creation_date']) : null);
                $author = null;
                if (null !== $section['author']) {
                    $author = $this->userRepository->findOneByUsername($section['author']);
                }
                if (null === $author) {
                    $author = $loggedUser;
                }
                $entitySection->setAuthor($author);
                $parentSection = null;
                if (null !== $section['parent_id']) {
                    $parentSection = $sectionsMap[$section['parent_id']];
                    $entitySection->setParent($parentSection);
                }
                if ($section['is_root']) {
                    $wiki->setRoot($entitySection);
                    $this->om->persist($wiki);
                }

                foreach ($section['contributions'] as $contribution) {
                    $contributionData = $contribution['contribution'];
                    $entityContribution = new Contribution();
                    $entityContribution->setSection($entitySection);
                    $entityContribution->setTitle($contributionData['title']);
                    $entityContribution->setCreationDate((null !== $contributionData['creation_date']) ? new \DateTime('@'.$contributionData['creation_date']) : null);
                    $contributor = null;
                    if (null !== $contributionData['contributor']) {
                        $contributor = $this->userRepository->findOneByUsername($contributionData['contributor']);
                    }
                    if (null === $contributor) {
                        $contributor = $loggedUser;
                    }
                    $entityContribution->setContributor($contributor);
                    $text = file_get_contents(
                        $rootPath.DIRECTORY_SEPARATOR.$contributionData['path']
                    );
                    $entityContribution->setText($text);
                    if ($contributionData['is_active']) {
                        $entitySection->setActiveContribution($entityContribution);
                        if (null !== $parentSection) {
                            $this->sectionRepository->persistAsLastChildOf($entitySection, $parentSection);
                        } else {
                            $this->sectionRepository->persistAsFirstChild($entitySection);
                        }
                    }
                    $this->om->persist($entityContribution);
                }
                $sectionsMap[$section['id']] = $entitySection;
            }
        }

        return $wiki;
    }

    /**
     * Exports a Wiki resource
     * according to the description found in WikiImporter.
     *
     * @param Workspace $workspace
     * @param array     $files
     * @param Wiki      $object
     *
     * @return array
     */
    public function exportWiki(Workspace $workspace, array &$files, Wiki $object)
    {
        // Getting all sections and building array
        $rootSection = $object->getRoot();
        $sections = $this->sectionRepository->children($rootSection);
        array_unshift($sections, $rootSection);
        $sectionsArray = [];
        foreach ($sections as $section) {
            //Getting all contributions and building contributions array
            $activeContribution = $section->getActiveContribution();
            $contributions = $this->contributionRepository->findAllButActiveForSection($section);
            $contributionsArray = [];
            array_unshift($contributions, $activeContribution);
            foreach ($contributions as $contribution) {
                $uid = uniqid().'.txt';
                $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$uid;
                file_put_contents($tmpPath, $contribution->getText());
                $files[$uid] = $tmpPath;

                $contributionArray = [
                    'is_active' => $contribution->getId() === $activeContribution->getId(),
                    'title' => $contribution->getTitle(),
                    'contributor' => $contribution->getContributor()->getUsername(),
                    'creation_date' => $contribution->getCreationDate(),
                    'path' => $uid,
                ];

                $contributionsArray[] = ['contribution' => $contributionArray];
            }
            $sectionArray = [
                'id' => $section->getId(),
                'parent_id' => (null !== $section->getParent()) ? $section->getParent()->getId() : null,
                'is_root' => $section->isRoot(),
                'visible' => $section->getVisible(),
                'creation_date' => $section->getCreationDate(),
                'author' => $section->getAuthor()->getUsername(),
                'deleted' => $section->getDeleted(),
                'deletion_date' => $section->getDeletionDate(),
                'contributions' => $contributionsArray,
            ];

            $sectionsArray[] = $sectionArray;
        }

        $data = [
            'options' => [
                'mode' => $object->getMode(),
                'display_section_numbers' => $object->getDisplaySectionNumbers(),
            ],
            'sections' => $sectionsArray,
        ];

        return $data;
    }
}
