<?php

namespace Icap\WikiBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Repository\SectionRepository;
use Icap\WikiBundle\Serializer\SectionSerializer;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SectionManager
{
    private SectionRepository $sectionRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly SectionSerializer $sectionSerializer
    ) {
        $this->sectionRepository = $om->getRepository('Icap\WikiBundle\Entity\Section');
    }

    public function getSerializedSectionTree(Wiki $wiki, User $user = null, $isAdmin = false): array
    {
        $tree = $this->sectionRepository->buildSectionTree($wiki, $user, $isAdmin);

        return $this->sectionSerializer->serializeSectionTreeNode($wiki, $tree[0]);
    }

    public function serializeSection(Section $section, $options = [], $isNew = false): array
    {
        return $this->sectionSerializer->serialize($section, $options, $isNew);
    }

    public function setActiveContribution(Section $section, Contribution $contribution): void
    {
        $section->setActiveContribution($contribution);
        $this->om->persist($section);
        $this->om->flush();
    }

    public function updateSection(Section $section, User $user, $data): void
    {
        $this->sectionSerializer->deserialize($data, $user, $section);
        if (isset($data['move']['section']) && $data['move']['section'] !== $section->getUuid()) {
            $this->moveSection($section, $data['move']['section'], 'before' === $data['move']['direction']);
        }

        $this->om->persist($section);
        $this->om->flush();
    }

    public function updateSectionVisibility(Section $section, bool $visibility): void
    {
        if ($section->getVisible() !== $visibility) {
            $section->setVisible($visibility);
            $this->om->persist($section);
            $this->om->flush();
        }
    }

    public function createSection(Wiki $wiki, Section $section, User $user, $isAdmin, $data): Section
    {
        $newSection = $this->sectionSerializer->deserialize($data, $user);
        $newSection->setWiki($wiki);
        if (Wiki::MODERATE_MODE === $wiki->getMode() && !$isAdmin) {
            $newSection->setVisible(false);
        }
        $this->sectionRepository->persistAsLastChildOf($newSection, $section);
        $this->om->flush();

        return $newSection;
    }

    public function restoreSections(Wiki $wiki, $ids = []): void
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
    ): void {
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

    public function moveSection(Section $section, $newSectionId, $before = false): void
    {
        $newSection = $this->sectionRepository->findOneBy(['uuid' => $newSectionId, 'wiki' => $section->getWiki()]);
        if ($before) {
            $this->sectionRepository->persistAsPrevSiblingOf($section, $newSection);
        } else {
            $this->sectionRepository->persistAsNextSiblingOf($section, $newSection);
        }
        $section->setMoved(true);
    }

    private function restoreSection(Section $section): void
    {
        if ($section->getDeleted()) {
            $this->sectionRepository->restoreSection($section);
        }
    }

    private function deleteSection(Section $section, $withChildren = false): void
    {
        if (!$section->getDeleted()) {
            // Soft delete
            if ($withChildren) {
                $this->sectionRepository->deleteSubtree($section);
            } else {
                $this->sectionRepository->deleteFromTree($section);
            }
        } else {
            // Hard delete
            $this->om->remove($section);
            $this->om->flush();
        }
    }
}
