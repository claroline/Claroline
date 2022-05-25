<?php

namespace Icap\WikiBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Event\Log\LogSectionCreateEvent;
use Icap\WikiBundle\Event\Log\LogSectionDeleteEvent;
use Icap\WikiBundle\Event\Log\LogSectionMoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRemoveEvent;
use Icap\WikiBundle\Event\Log\LogSectionRestoreEvent;
use Icap\WikiBundle\Event\Log\LogSectionUpdateEvent;
use Icap\WikiBundle\Serializer\SectionSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SectionManager
{
    /** @var ObjectManager */
    protected $om;

    /** @var \Icap\WikiBundle\Repository\SectionRepository */
    protected $sectionRepository;

    /** @var SectionSerializer */
    protected $sectionSerializer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param $eventDispatcher
     */
    public function __construct(
        ObjectManager $om,
        SectionSerializer $sectionSerializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->sectionRepository = $om->getRepository('Icap\WikiBundle\Entity\Section');
        $this->sectionSerializer = $sectionSerializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getSerializedSectionTree(Wiki $wiki, User $user = null, $isAdmin = false)
    {
        $tree = $this->sectionRepository->buildSectionTree($wiki, $user, $isAdmin);

        return $this->sectionSerializer->serializeSectionTreeNode($wiki, $tree[0]);
    }

    public function serializeSection(Section $section, $options = [], $isNew = false)
    {
        return $this->sectionSerializer->serialize($section, $options, $isNew);
    }

    public function setActiveContribution(Section $section, Contribution $contribution)
    {
        $section->setActiveContribution($contribution);
        $this->om->persist($section);
        $this->om->flush();
    }

    public function updateSection(Section $section, User $user, $data)
    {
        $this->sectionSerializer->deserialize($data, $user, $section);
        if (isset($data['move']['section']) && $data['move']['section'] !== $section->getUuid()) {
            $this->moveSection($section, $data['move']['section'], 'before' === $data['move']['direction']);
        }
        $this->om->persist($section);
        $this->om->flush();

        $this->dispatch(new LogSectionUpdateEvent($section->getWiki(), $section, []));

        if ($section->hasMoved()) {
            $this->dispatch(new LogSectionMoveEvent($section->getWiki(), $section, []));
        }
    }

    public function updateSectionVisibility(Section $section, bool $visibility)
    {
        if ($section->getVisible() !== $visibility) {
            $section->setVisible($visibility);
            $this->om->persist($section);
            $this->om->flush();
        }
    }

    public function createSection(Wiki $wiki, Section $section, User $user, $isAdmin, $data)
    {
        $newSection = $this->sectionSerializer->deserialize($data, $user);
        $newSection->setWiki($wiki);
        if (Wiki::MODERATE_MODE === $wiki->getMode() && !$isAdmin) {
            $newSection->setVisible(false);
        }
        $this->sectionRepository->persistAsLastChildOf($newSection, $section);
        $this->om->flush();

        $this->dispatch(new LogSectionCreateEvent($wiki, $newSection));

        return $newSection;
    }

    public function restoreSections(Wiki $wiki, $ids = [])
    {
        $sections = $this->sectionRepository->findSectionsBy([
            'uuid' => $ids,
            'wiki' => $wiki,
            'deleted' => true,
        ]);

        foreach ($sections as $section) {
            $this->restoreSection($section);
        }
    }

    public function deleteSections(
        Wiki $wiki,
        $ids,
        $withChildren = false,
        $permanently = false,
        $isAdmin = false,
        User $user = null
    ) {
        if (!$isAdmin && $permanently) {
            throw new AccessDeniedException('You cannot delete permanently any wiki sections');
        }

        $sections = $this->sectionRepository->findSectionsBy([
            'uuid' => $ids,
            'wiki' => $wiki,
            'deleted' => $permanently,
        ]);

        /** @var Section $section */
        foreach ($sections as $section) {
            if ($isAdmin || (!$section->getDeleted() && $section->getAuthor()->getId() === $user->getId())) {
                $this->deleteSection($section, $withChildren);
            } else {
                throw new AccessDeniedException('You cannot delete this section');
            }
        }
    }

    public function moveSection(Section $section, $newSectionId, $before = false)
    {
        $newSection = $this->sectionRepository->findOneBy(['uuid' => $newSectionId, 'wiki' => $section->getWiki()]);
        if ($before) {
            $this->sectionRepository->persistAsPrevSiblingOf($section, $newSection);
        } else {
            $this->sectionRepository->persistAsNextSiblingOf($section, $newSection);
        }
        $section->setMoved(true);
    }

    public function getArchivedSectionsForPosition(Section $section)
    {
        $sections = $this->sectionRepository->findSectionsForPosition($section);
        $archivedSections = [];
        $prefixesArray = [];
        $childrens = [];
        foreach ($sections as $simpleSection) {
            if (isset($childrens[$simpleSection['parentId']])) {
                ++$childrens[$simpleSection['parentId']];
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

    private function dispatch($event)
    {
        $this->eventDispatcher->dispatch($event, 'log');
    }

    private function restoreSection(Section $section)
    {
        if ($section->getDeleted()) {
            $this->sectionRepository->restoreSection($section);
            $this->dispatch(new LogSectionRestoreEvent($section->getWiki(), $section));
        }
    }

    private function deleteSection(Section $section, $withChildren = false)
    {
        if (!$section->getDeleted()) {
            // Soft delete
            if ($withChildren) {
                $this->sectionRepository->deleteSubtree($section);
            } else {
                $this->sectionRepository->deleteFromTree($section);
            }

            $this->dispatch(new LogSectionDeleteEvent($section->getWiki(), $section));
        } else {
            // Hard delete
            $this->om->remove($section);
            $this->om->flush();

            $this->dispatch(new LogSectionRemoveEvent($section->getWiki(), $section));
        }
    }
}
