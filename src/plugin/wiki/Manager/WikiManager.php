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
use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Event\Log\LogWikiConfigureEvent;
use Icap\WikiBundle\Serializer\WikiSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var \Claroline\CoreBundle\Repository\User\UserRepository
     */
    private $userRepository;

    /** @var WikiSerializer */
    private $wikiSerializer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        ObjectManager $om,
        WikiSerializer $wikiSerializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->wikiSerializer = $wikiSerializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->sectionRepository = $this->om->getRepository(Section::class);
        $this->contributionRepository = $this->om->getRepository(Contribution::class);
        $this->userRepository = $this->om->getRepository(User::class);
    }

    public function updateWiki(Wiki $wiki, $data)
    {
        $this->wikiSerializer->deserialize($data, $wiki);
        $this->om->persist($wiki);
        $uow = $this->om->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($wiki);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogWikiConfigureEvent($wiki, $changeSet), 'log');
    }

    public function serializeWiki(Wiki $wiki)
    {
        return $this->wikiSerializer->serialize($wiki);
    }

    public function copyWiki(Wiki $orgWiki, Wiki $newWiki, $loggedUser)
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
