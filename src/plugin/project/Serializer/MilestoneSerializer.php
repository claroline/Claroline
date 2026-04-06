<?php

namespace Claroline\ProjectBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ProjectBundle\Entity\Milestone;
use Doctrine\Persistence\ObjectRepository;

class MilestoneSerializer
{
    private ObjectRepository $milestoneRepo;

    public function __construct(ObjectManager $om)
    {
        $this->milestoneRepo = $om->getRepository(Milestone::class);
    }

    public function getName(): string
    {
        return 'project_milestone';
    }

    public function getClass(): string
    {
        return Milestone::class;
    }

    public function serialize(Milestone $milestone): array
    {
        return [
            'id' => $milestone->getUuid(),
            'title' => $milestone->getTitle(),
            'instructions' => $milestone->getInstructions(),
            'position' => $milestone->getPosition(),
            'deadline' => [
                'type' => $milestone->getDeadlineType(),
                'date' => DateNormalizer::normalize($milestone->getDeadlineDate()),
                'days' => $milestone->getDeadlineDays(),
            ],
        ];
    }

    public function deserialize(array $data): Milestone
    {
        $milestone = null;

        if (isset($data['id'])) {
            $milestone = $this->milestoneRepo->findOneBy(['uuid' => $data['id']]);
        }

        if (empty($milestone)) {
            $milestone = new Milestone();
            if (isset($data['id'])) {
                $milestone->setUuid($data['id']);
            }
        }

        if (isset($data['title'])) {
            $milestone->setTitle($data['title']);
        }
        if (isset($data['instructions'])) {
            $milestone->setInstructions($data['instructions']);
        }
        if (isset($data['position'])) {
            $milestone->setPosition($data['position']);
        }
        if (isset($data['deadline'])) {
            if (isset($data['deadline']['type'])) {
                $milestone->setDeadlineType($data['deadline']['type']);
            }
            if (array_key_exists('date', $data['deadline'])) {
                $milestone->setDeadlineDate(DateNormalizer::denormalize($data['deadline']['date']));
            }
            if (array_key_exists('days', $data['deadline'])) {
                $milestone->setDeadlineDays($data['deadline']['days']);
            }
        }

        return $milestone;
    }
}
