<?php

namespace Icap\WikiBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;

class SectionSerializer
{
    use SerializerTrait;

    /** @var ContributionSerializer */
    private $contributionSerializer;

    /** @var UserRepository */
    private $userRepo;

    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        ObjectManager $om,
        ContributionSerializer $contributionSerializer,
        UserSerializer $userSerializer
    ) {
        $this->contributionSerializer = $contributionSerializer;
        $this->userSerializer = $userSerializer;
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName()
    {
        return 'wiki_section';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Section::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/wiki/section.json';
    }

    public function serialize(Section $section, $options = [], $isNew = false): array
    {
        $author = $section->getAuthor();
        $extraMeta = [];
        if (in_array(Options::DEEP_SERIALIZE, $options) && !$section->isRoot()) {
            $extraMeta = [
                'new' => $isNew,
                'parent' => $section->getParent()->getUuid(),
                'moved' => $section->hasMoved(),
            ];
        }

        return [
            'id' => $section->getUuid(),
            'activeContribution' => $this->contributionSerializer->serialize($section->getActiveContribution()),
            'meta' => array_merge($extraMeta, [
                'createdAt' => $section->getCreationDate()->format('Y-m-d H:i'),
                'visible' => $section->getVisible(),
                'deletedAt' => $section->getDeleted() ? $section->getDeletionDate()->format('Y-m-d H:i') : null,
                'creator' => null === $author ?
                    null :
                    $this->userSerializer->serialize($author, [Options::SERIALIZE_MINIMAL]),
            ]),
            'children' => [],
        ];
    }

    /**
     * Serializes a section tree, returned from Gedmo tree extension.
     *
     * @param $tree
     *
     * @return array
     */
    public function serializeSectionTree(Wiki $wiki, $tree)
    {
        return $this->serializeSectionTreeNode($wiki, $tree[0]);
    }

    public function serializeSectionTreeNode(Wiki $wiki, $node)
    {
        $children = [];
        if (!empty($node['__children'])) {
            foreach ($node['__children'] as $child) {
                $children[] = $this->serializeSectionTreeNode($wiki, $child);
            }
        }

        $author = null;

        if ($node['author']) {
            $author = $node['author'];
        }

        return [
            'id' => $node['uuid'],
            'meta' => [
                'createdAt' => $node['creationDate']->format('Y-m-d H:i'),
                'visible' => $node['visible'],
                'creator' => null === $author ? null : $author,
            ],
            'activeContribution' => $this->contributionSerializer->serializeFromSectionNode($node),
            'children' => $children,
        ];
    }

    public function deserialize(array $data, User $user, Section $section = null): Section
    {
        if (empty($section)) {
            $section = new Section();
            $section->refreshUuid();
            $section->setAuthor($user);
        }
        if ($data['activeContribution']) {
            $activeContribution = $this->contributionSerializer->deserialize($data['activeContribution'], $user, $section);
            $section->setActiveContribution($activeContribution);
        }

        return $section;
    }
}
