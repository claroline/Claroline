<?php

namespace Claroline\AudioPlayerBundle\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AudioPlayerBundle\Entity\Resource\Section;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;

class SectionSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    private $resourceNodeRepo;
    private $userRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    /**
     * @return array
     */
    public function serialize(Section $section, array $options = [])
    {
        $serialized = [
            'id' => $section->getUuid(),
            'type' => $section->getType(),
            'title' => $section->getTitle(),
            'start' => $section->getStart(),
            'end' => $section->getEnd(),
            'color' => $section->getColor(),
            'showTranscript' => $section->getShowTranscript(),
            'transcript' => $section->getTranscript(),
            'commentsAllowed' => $section->isCommentsAllowed(),
            'showHelp' => $section->getShowHelp(),
            'help' => $section->getHelp(),
            'showAudio' => $section->getShowAudio(),
            'audioUrl' => $section->getAudioUrl(),
            'audioDescription' => $section->getAudioDescription(),
        ];

        return $serialized;
    }

    /**
     * @param array $data
     *
     * @return Section
     */
    public function deserialize($data, Section $section, array $options = [])
    {
        $this->sipe('type', 'setType', $data, $section);
        $this->sipe('title', 'setTitle', $data, $section);
        $this->sipe('start', 'setStart', $data, $section);
        $this->sipe('end', 'setEnd', $data, $section);
        $this->sipe('color', 'setColor', $data, $section);
        $this->sipe('showTranscript', 'setShowTranscript', $data, $section);
        $this->sipe('transcript', 'setTranscript', $data, $section);
        $this->sipe('commentsAllowed', 'setCommentsAllowed', $data, $section);
        $this->sipe('showHelp', 'setShowHelp', $data, $section);
        $this->sipe('help', 'setHelp', $data, $section);
        $this->sipe('showAudio', 'setShowAudio', $data, $section);
        $this->sipe('audioUrl', 'setAudioUrl', $data, $section);
        $this->sipe('audioDescription', 'setAudioDescription', $data, $section);

        if (isset($data['meta']['resourceNode']['id']) && !$section->getResourceNode()) {
            $resourceNode = $this->resourceNodeRepo->findOneBy(['uuid' => $data['meta']['resourceNode']['id']]);

            if ($resourceNode) {
                $section->setResourceNode($resourceNode);
            }
        }
        if (isset($data['meta']['user']['id']) && !$section->getUser()) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['meta']['user']['id']]);

            if ($user) {
                $section->setUser($user);
            }
        }

        return $section;
    }
}
