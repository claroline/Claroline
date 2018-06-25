<?php

namespace Icap\WikiBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Icap\WikiBundle\Entity\Contribution;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Repository\SectionRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @DI\Service("claroline.serializer.wiki.section.contribution")
 * @DI\Tag("claroline.serializer")
 */
class ContributionSerializer
{
    use SerializerTrait;

    /** @var SectionRepository */
    private $sectionRepo;

    /** @var UserRepository */
    private $userRepo;

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * ContributionSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "userSerializer"     = @DI\Inject("claroline.serializer.user")
     * })
     */
    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer
    ) {
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
        $this->sectionRepo = $om->getRepository('Icap\WikiBundle\Entity\Section');
        $this->userSerializer = $userSerializer;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Icap\WikiBundle\Entity\Contribution';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/wiki/contribution.json';
    }

    /**
     * @param Contribution $contribution
     *
     * @return array - The serialized representation of a contribution
     */
    public function serialize(Contribution $contribution)
    {
        $contributor = $contribution->getContributor();

        return [
            'id' => $contribution->getUuid(),
            'title' => $contribution->getTitle(),
            'text' => $contribution->getText(),
            'meta' => [
                'createdAt' => $contribution->getCreationDate()->format('Y-m-d H:i'),
                'creator' => null === $contributor ?
                    null :
                    $this->userSerializer->serialize($contributor, [Options::SERIALIZE_MINIMAL]),
            ],
        ];
    }

    public function serializeFromSectionNode($sectionNode)
    {
        $contribution = $sectionNode['activeContribution'];

        return [
            'id' => $contribution['uuid'],
            'title' => $contribution['title'],
            'text' => $contribution['text'],
            'meta' => [
                'createdAt' => $contribution['creationDate']->format('Y-m-d H:i'),
            ],
        ];
    }

    /**
     * @param array               $data
     * @param User                $user
     * @param Section             $section
     * @param Contribution | null $contribution
     *
     * @return Contribution - The deserialized contribution entity
     */
    public function deserialize($data, User $user, Section $section, Contribution $contribution = null)
    {
        if (empty($contribution)) {
            $contribution = new Contribution();
            $contribution->setSection($section);
            $contribution->setContributor($user);
            $contribution->refreshUuid();
        }
        $this->sipe('title', 'setTitle', $data, $contribution);
        $this->sipe('text', 'setText', $data, $contribution);
        if (empty($contribution->getTitle()) && !$section->isRoot()) {
            throw new BadRequestHttpException('Title cannot be blank');
        }

        return $contribution;
    }
}
