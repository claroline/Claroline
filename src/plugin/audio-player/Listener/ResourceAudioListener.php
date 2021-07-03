<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AudioPlayerBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AudioPlayerBundle\Entity\Resource\AudioParams;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Claroline\AudioPlayerBundle\Manager\AudioPlayerManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceAudioListener
{
    /** @var AudioPlayerManager */
    private $manager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        AudioPlayerManager $manager,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
    }

    public function onResourceAudioLoad(LoadFileEvent $event)
    {
        /** @var User|string $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $resourceNode = $event->getResource()->getResourceNode();
        $audioParams = $this->manager->getAudioParams($resourceNode);
        $audioData = $this->serializer->serialize($audioParams);

        $audioData['sections'] = [];
        switch ($audioParams->getSectionsType()) {
            case AudioParams::MANAGER_TYPE:
                $audioData['sections'] = array_values(array_map(function (Section $section) use ($user) {
                    $serializedSection = $this->serializer->serialize($section);

                    if ($user instanceof User) {
                        $userComment = $this->manager->getSectionUserComment($section, $user);

                        if ($userComment) {
                            $serializedSection['comment'] = $this->serializer->serialize($userComment);
                        }
                    }

                    return $serializedSection;
                }, $this->manager->getManagerSections($resourceNode)));
                break;
            case AudioParams::USER_TYPE:
                if ($user instanceof User) {
                    $audioData['sections'] = array_values(array_map(function (Section $section) use ($user) {
                        $serializedSection = $this->serializer->serialize($section);
                        $userComment = $this->manager->getSectionUserComment($section, $user);

                        if ($userComment) {
                            $serializedSection['comment'] = $this->serializer->serialize($userComment);
                        }

                        return $serializedSection;
                    }, $this->manager->getUserSections($resourceNode, $user)));
                }
                break;
        }

        $event->setData(array_merge($audioData, $event->getData()));
    }

    public function onResourceAudioDeserialize(GenericDataEvent $event)
    {
        $eventData = $event->getData();
        $resourceNode = $eventData['resourceNode'];
        $data = $eventData['data'];

        if ($resourceNode) {
            $audioParams = $this->manager->getAudioParams($resourceNode);
            $this->serializer->get(AudioParams::class)->deserialize($data, $audioParams);
            $this->manager->deserializeSections($resourceNode, $data);
        }
    }
}
