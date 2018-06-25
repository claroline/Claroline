<?php

namespace Icap\WikiBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Entity\Wiki;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.wiki.section")
 * @DI\Tag("claroline.serializer")
 */
class SectionSerializer
{
    use SerializerTrait;

    /** @var ContributionSerializer */
    private $contributionSerializer;

    /** @var UserRepository */
    private $userRepo;

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * SectionSerializer constructor.
     *
     * * @DI\InjectParams({
     *     "om"                         = @DI\Inject("claroline.persistence.object_manager"),
     *     "contributionSerializer"     = @DI\Inject("claroline.serializer.wiki.section.contribution"),
     *     "userSerializer"             = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param ContributionSerializer $contributionSerializer
     */
    public function __construct(
        ObjectManager $om,
        ContributionSerializer $contributionSerializer,
        UserSerializer $userSerializer
    ) {
        $this->contributionSerializer = $contributionSerializer;
        $this->userSerializer = $userSerializer;
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Icap\WikiBundle\Entity\Section';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/wiki/section.json';
    }

    /**
     * @param Section $section
     *
     * @return array - The serialized representation of a contribution
     */
    public function serialize(Section $section, $options = [], $isNew = false)
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
     * @param Wiki $wiki
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

        if (!empty($node['author'])) {
            foreach ($node['author'] as $key => $value) {
                if ($value instanceof \stdClass || $value instanceof \DateTime) {
                    unset($node['author'][$key]);
                }
            }
            $author = $this->userSerializer->serialize(
                $this->userSerializer->deserialize($node['author'], new User()),
                [Options::SERIALIZE_MINIMAL]
            );
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

    /**
     * @param array          $data
     * @param User           $user
     * @param Section | null $section
     *
     * @return Section - The deserialized section entity
     */
    public function deserialize($data, User $user, Section $section = null)
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
