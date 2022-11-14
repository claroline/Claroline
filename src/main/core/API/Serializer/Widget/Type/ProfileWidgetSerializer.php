<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Type\ProfileWidget;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileWidgetSerializer
{
    /** @var UserSerializer */
    private $userSerializer;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * WidgetInstanceSerializer constructor.
     *
     * @param TokenStorageInterface $tokenStorage,
     */
    public function __construct(TokenStorageInterface $tokenStorage, UserSerializer $userSerializer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'profile_widget';
    }

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
