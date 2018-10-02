<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Widget\Type\ProfileWidget;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.widget_profile")
 * @DI\Tag("claroline.serializer")
 */
class ProfileWidgetSerializer
{
    /** @var UserSerializer */
    private $userSerializer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * WidgetInstanceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param TokenStorageInterface $tokenStorage,
     * @param UserSerializer        $userSerializer
     */
    public function __construct(TokenStorageInterface $tokenStorage, UserSerializer $userSerializer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return ProfileWidget::class;
    }

    public function serialize(ProfileWidget $widget, array $options = []): array
    {
        $user = $widget->isCurrentUser() ?
            $this->tokenStorage->getToken()->getUser() instanceof User ? $this->tokenStorage->getToken()->getUser() : null :
            $widget->getUser();

        if (!$user) {
            return [
              'username' => 'anon',
            ];
        }

        return $this->userSerializer->serialize($user);
    }

    public function deserialize($data, ProfileWidget $widget, array $options = []): ProfileWidget
    {
        return $widget;
    }
}
