<?php

namespace Icap\WikiBundle\Listener\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use Icap\WikiBundle\Serializer\WikiSerializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WikiListener
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    /** @var WikiSerializer */
    private $serializer;

    /** @var WikiManager */
    private $wikiManager;

    /** @var SectionManager */
    private $sectionManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $objectManager,
        WikiSerializer $serializer,
        WikiManager $wikiManager,
        SectionManager $sectionManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $objectManager;
        $this->serializer = $serializer;
        $this->wikiManager = $wikiManager;
        $this->sectionManager = $sectionManager;
        $this->authorization = $authorization;
    }

    /**
     * Loads a Wiki resource.
     */
    public function load(LoadResourceEvent $event)
    {
        $resourceNode = $event->getResourceNode();

        /** @var Wiki $wiki */
        $wiki = $event->getResource();
        $sectionTree = $this->sectionManager->getSerializedSectionTree(
            $wiki,
            $this->tokenStorage->getToken()->getUser() instanceof User ? $this->tokenStorage->getToken()->getUser() : null,
            $this->checkPermission('EDIT', $resourceNode)
        );

        $event->setData([
            'wiki' => $this->serializer->serialize($wiki),
            'sections' => $sectionTree,
        ]);

        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Wiki $wiki */
        $wiki = $event->getResource();
        /** @var Wiki $copy */
        $copy = $event->getCopy();

        $newWiki = $this->wikiManager->copyWiki($wiki, $copy, $this->tokenStorage->getToken()->getUser());

        $event->setCopy($newWiki);
        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        /** @var Wiki $wiki */
        $wiki = $event->getResource();

        $event->setData([
            'root' => $this->sectionManager->getSerializedSectionTree($wiki, null, true),
        ]);
    }

    public function onImport(ImportResourceEvent $event)
    {
        $data = $event->getData();
        /** @var Wiki $wiki */
        $wiki = $event->getResource();

        $rootSection = $data['root'];
        $wiki->buildRoot();
        $root = $wiki->getRoot();

        if (isset($rootSection['children'])) {
            $children = $rootSection['children'];

            foreach ($children as $child) {
                $section = $this->importSection($child, $wiki);
                $section->setWiki($wiki);
                $section->setParent($root);

                $this->om->getRepository(Section::class)->persistAsLastChildOf($section, $root);
            }
        }
    }

    private function importSection(array $data, Wiki $wiki)
    {
        $section = new Section();
        $contrib = new Contribution();
        $contrib->setTitle($data['activeContribution']['title']);
        $contrib->setText($data['activeContribution']['text']);
        $contrib->setSection($section);
        $section->setActiveContribution($contrib);
        $this->om->persist($contrib);

        if (isset($data['children'])) {
            foreach ($data['children'] as $child) {
                $childSec = $this->importSection($child, $wiki);
                $childSec->setParent($section);
                $this->om->getRepository(Section::class)->persistAsLastChildOf($childSec, $section);
            }
        }

        $section->setWiki($wiki);

        return $section;
    }
}
