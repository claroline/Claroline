<?php

namespace Icap\WikiBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Manager\SectionManager;
use Icap\WikiBundle\Manager\WikiManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WikiListener extends ResourceComponent
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly WikiManager $wikiManager,
        private readonly SectionManager $sectionManager
    ) {
    }

    public static function getName(): string
    {
        return 'icap_wiki';
    }

    /** @var Wiki $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $sectionTree = $this->sectionManager->getSerializedSectionTree(
            $resource,
            $this->tokenStorage->getToken()?->getUser() instanceof User ? $this->tokenStorage->getToken()?->getUser() : null,
            $this->authorization->isGranted('EDIT', $resource->getResourceNode())
        );

        return [
            'wiki' => $this->serializer->serialize($resource),
            'sections' => $sectionTree,
        ];
    }

    /**
     * @var Wiki $original
     * @var Wiki $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->wikiManager->copyWiki($original, $copy, $this->tokenStorage->getToken()?->getUser());
    }

    /** @var Wiki $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        return [
            'root' => $this->sectionManager->getSerializedSectionTree($resource, null, true),
        ];
    }

    /** @var Wiki $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        $rootSection = $data['root'];
        $resource->buildRoot();
        $root = $resource->getRoot();

        if (isset($rootSection['children'])) {
            $children = $rootSection['children'];

            foreach ($children as $child) {
                $section = $this->importSection($child, $resource);
                $section->setWiki($resource);
                $section->setParent($root);

                $this->om->getRepository(Section::class)->persistAsLastChildOf($section, $root);
            }
        }
    }

    private function importSection(array $data, Wiki $wiki): Section
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
