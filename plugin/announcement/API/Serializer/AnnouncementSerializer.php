<?php

namespace Claroline\AnnouncementBundle\API\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\CoreBundle\API\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.announcement")
 * @DI\Tag("claroline.serializer")
 */
class AnnouncementSerializer
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * AnnouncementSerializer constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param UserSerializer        $userSerializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserSerializer $userSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
    }

    /**
     * @param Announcement $announce
     *
     * @return array
     */
    public function serialize(Announcement $announce)
    {
        return [
            'id' => $announce->getUuid(),
            'title' => $announce->getTitle(),
            'content' => $announce->getContent(),
            'meta' => [
                'created' => $announce->getCreationDate()->format('Y-m-d\TH:i:s'),
                'creator' => $announce->getCreator() ? $this->userSerializer->serialize($announce->getCreator()) : null,
                'publishedAt' => $announce->getPublicationDate() ? $announce->getPublicationDate()->format('Y-m-d\TH:i:s') : null,
                'author' => $announce->getAnnouncer(),
                'notifyUsers' => !empty($announce->getTask()),
            ],
            'restrictions' => [
                'visible' => $announce->isVisible(),
                'visibleFrom' => $announce->getVisibleFrom() ? $announce->getVisibleFrom()->format('Y-m-d\TH:i:s') : null,
                'visibleUntil' => $announce->getVisibleUntil() ? $announce->getVisibleUntil()->format('Y-m-d\TH:i:s') : null,
            ],
        ];
    }

    /**
     * @param array        $data
     * @param Announcement $announce
     *
     * @return Announcement
     */
    public function deserialize(array $data, Announcement $announce)
    {
        $announce = $announce ?: new Announcement();

        $announce->setTitle($data['title']);
        $announce->setContent($data['content']);
        $announce->setAnnouncer($data['meta']['author']);

        if (empty($announce->getCreator())) {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof User) {
                // only get authenticated user
                $announce->setCreator($currentUser);
            }
        }

        // calculate visibility restrictions
        $announce->setVisible($data['restrictions']['visible']);

        $visibleFrom = null;
        if (!empty($data['restrictions']['visibleFrom'])) {
            $visibleFrom = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['restrictions']['visibleFrom']);
        }
        $announce->setVisibleFrom($visibleFrom);

        $visibleUntil = null;
        if (!empty($data['restrictions']['visibleUntil'])) {
            $visibleUntil = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['restrictions']['visibleUntil']);
        }
        $announce->setVisibleFrom($visibleUntil);

        // calculate publication date
        if (!$announce->isVisible()) {
            $announce->setPublicationDate(null);
        } else {
            $now = new \DateTime();
            if (empty($announce->getVisibleFrom()) || $announce->getVisibleFrom() < $now) {
                $announce->setPublicationDate($now);
            } else {
                $announce->setPublicationDate($announce->getVisibleFrom());
            }
        }

        return $announce;
    }
}
