<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ForumBundle\Entity\Forum;

class ForumSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Forum::class;
    }

    public function getName(): string
    {
        return 'forum';
    }

    public function getSchema(): string
    {
        return '#/plugin/forum/forum.json';
    }

    public function getSamples(): string
    {
        return '#/plugin/forum/forum';
    }

    public function serialize(Forum $forum, ?array $options = []): array
    {
        return [
            'id' => $forum->getUuid(),
            'moderation' => $forum->getValidationMode(),
            'display' => [
                'description' => $forum->getOverviewMessage(),
                'showOverview' => $forum->getShowOverview(),
                'subjectDataList' => $forum->getDataListOptions(),
                'lastMessagesCount' => $forum->getDisplayMessages(),
                'messageOrder' => $forum->getMessageOrder(),
                'expandComments' => $forum->getExpandComments(),
            ],
            'restrictions' => [
                'lockDate' => DateNormalizer::normalize($forum->getLockDate()),
            ],
        ];
    }

    public function deserialize(array $data, Forum $forum, ?array $options = []): Forum
    {
        $this->sipe('moderation', 'setValidationMode', $data, $forum);
        $this->sipe('display.lastMessagesCount', 'setDisplayMessage', $data, $forum);
        $this->sipe('display.subjectDataList', 'setDataListOptions', $data, $forum);
        $this->sipe('display.description', 'setOverviewMessage', $data, $forum);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $forum);
        $this->sipe('display.messageOrder', 'setMessageOrder', $data, $forum);
        $this->sipe('display.expandComments', 'setExpandComments', $data, $forum);

        if (isset($data['restrictions'])) {
            if (isset($data['restrictions']['lockDate'])) {
                $forum->setLockDate(DateNormalizer::denormalize($data['restrictions']['lockDate']));
            }
        }

        return $forum;
    }
}
