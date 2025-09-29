<?php

namespace Claroline\EvaluationBundle\Serializer\Sequence;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Claroline\EvaluationBundle\Entity\Sequence\Assignment;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\Sequence\Step;
use Claroline\TemplateBundle\Entity\Template;
use Claroline\TemplateBundle\Serializer\TemplateSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SequenceSerializer
{
    use SerializerTrait;

    private ResourceNodeRepository $resourceNodeRepo;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly TemplateSerializer $templateSerializer,
        private readonly UserSerializer $userSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer,
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
        private readonly StepSerializer $stepSerializer,
        private readonly AssignmentSerializer $assignmentSerializer
    ) {
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
    }

    public function getClass(): string
    {
        return Sequence::class;
    }

    public function getSchema(): string
    {
        return '#/main/evaluation/sequence/sequence.json';
    }

    public function getName(): string
    {
        return 'sequence';
    }

    public function serialize(Sequence $sequence, array $options = []): array
    {
        $serializedWorkspace = null;
        if ($sequence->getWorkspace()) {
            $serializedWorkspace = $this->workspaceSerializer->serialize($sequence->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $sequence->getUuid(),
                'name' => $sequence->getName(),
                'code' => $sequence->getCode(),
                'poster' => $sequence->getPoster(),
                'meta' => [
                    'description' => $sequence->getDescription(),
                    'published' => $sequence->isPublished(), // not required but nice to have
                ],
                // for now this is required in the minimal representation to generate the correct sequence path
                'workspace' => $serializedWorkspace,
            ];
        }

        $serialized = [
            'id' => $sequence->getUuid(),
            'autoId' => $sequence->getId(),
            'name' => $sequence->getName(),
            'code' => $sequence->getCode(),
            'poster' => $sequence->getPoster(),
            'meta' => [
                'public' => $sequence->isPublic(),
                'description' => $sequence->getDescription(),
                'descriptionHtml' => $sequence->getDescriptionHtml(),
                'creator' => $sequence->getCreator() ?
                    $this->userSerializer->serialize($sequence->getCreator(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'createdAt' => DateNormalizer::normalize($sequence->getCreatedAt()),
                'updatedAt' => DateNormalizer::normalize($sequence->getUpdatedAt()),
                'published' => $sequence->isPublished(),
                'views' => $sequence->getViews(),
            ],
            'workspace' => $serializedWorkspace,
            'display' => [
                'numbering' => $sequence->getNumbering(),
                'pagination' => $sequence->getPagination(),
                'showScore' => $sequence->getShowScore(),
            ],
            'opening' => [
                'secondaryResources' => $sequence->getSecondaryResourcesTarget(),
            ],
            'steps' => array_values(array_map(function (Step $step) use ($options) {
                return $this->stepSerializer->serialize($step, $options);
            }, $sequence->getRootSteps())),
            'estimatedDuration' => $sequence->getEstimatedDuration(),
            'objective' => $sequence->getObjective(),
            'evaluation' => [
                'certified' => $sequence->isCertified(),
                'certificateTemplate' => $sequence->getCertificateTemplate() ?
                    $this->templateSerializer->serialize($sequence->getCertificateTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'estimatedDuration' => $sequence->getEstimatedDuration(), // deprecated
                'scoreTotal' => $sequence->getScoreTotal(),
                'successCondition' => $sequence->getSuccessCondition(),
                'endMessage' => $sequence->getEndMessage(),
                'successMessage' => $sequence->getSuccessMessage(),
                'failureMessage' => $sequence->getFailureMessage(),
            ],
            'overview' => [
                'resource' => $sequence->getOverviewResource() ? $this->resourceNodeSerializer->serialize($sequence->getOverviewResource(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            ],
            'end' => [
                'back' => [
                    'type' => $sequence->getEndBackType(),
                    'label' => $sequence->getEndBackLabel(),
                    'target' => $sequence->getEndBackTarget() ? $this->resourceNodeSerializer->serialize($sequence->getEndBackTarget(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
                ],
            ],
            'restrictions' => [
                'dates' => DateRangeNormalizer::normalize($sequence->getAccessibleFrom(), $sequence->getAccessibleUntil()),
                'code' => $sequence->getAccessCode(),
            ],
            'assignments' => array_map(function (Assignment $assignment) {
                return $this->assignmentSerializer->serialize($assignment, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $sequence->getAssignments()->toArray()),
            'tags' => $this->serializeTags($sequence),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $administrate = $this->authorization->isGranted('ADMINISTRATE', $sequence);

            $serialized['permissions'] = [
                'open' => $administrate || $this->authorization->isGranted('OPEN', $sequence),
                'delete' => $administrate,
                'follow' => $administrate || $this->authorization->isGranted('FOLLOW', $sequence),
                'edit' => $administrate || $this->authorization->isGranted('EDIT', $sequence),
                'administrate' => $administrate,
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, Sequence $sequence, array $options = []): Sequence
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $sequence);
            $this->sipe('slug', 'setSlug', $data, $sequence);
        } else {
            $sequence->refreshUuid();
        }

        $this->sipe('name', 'setName', $data, $sequence);
        $this->sipe('code', 'setCode', $data, $sequence);
        $this->sipe('poster', 'setPoster', $data, $sequence);
        $this->sipe('meta.published', 'setPublished', $data, $sequence);
        $this->sipe('meta.description', 'setDescription', $data, $sequence);
        $this->sipe('meta.descriptionHtml', 'setDescriptionHtml', $data, $sequence);
        $this->sipe('meta.public', 'setPublic', $data, $sequence);

        $this->sipe('display.numbering', 'setNumbering', $data, $sequence);
        $this->sipe('display.pagination', 'setPagination', $data, $sequence);
        $this->sipe('display.showScore', 'setShowScore', $data, $sequence);

        $this->sipe('opening.secondaryResources', 'setSecondaryResourcesTarget', $data, $sequence);
        $this->sipe('estimatedDuration', 'setEstimatedDuration', $data, $sequence);
        $this->sipe('objective', 'setObjective', $data, $sequence);

        if (isset($data['evaluation'])) {
            $this->sipe('evaluation.scoreTotal', 'setScoreTotal', $data, $sequence);
            $this->sipe('evaluation.endMessage', 'setEndMessage', $data, $sequence);
            $this->sipe('evaluation.successMessage', 'setSuccessMessage', $data, $sequence);
            $this->sipe('evaluation.failureMessage', 'setFailureMessage', $data, $sequence);
            $this->sipe('evaluation.successCondition', 'setSuccessCondition', $data, $sequence);
            $this->sipe('evaluation.certified', 'setCertified', $data, $sequence);

            if (array_key_exists('certificateTemplate', $data['evaluation'])) {
                $template = null;
                if (!empty($data['evaluation']['certificateTemplate']) && !empty($data['evaluation']['certificateTemplate']['id'])) {
                    $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['evaluation']['certificateTemplate']['id']]);
                }
                $sequence->setCertificateTemplate($template);
            }
        }

        if (empty($sequence->getWorkspace()) && !empty($data['workspace'])) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
            if ($workspace) {
                $sequence->setWorkspace($workspace);
            }
        }

        if (!empty($data['overview'])) {
            if (array_key_exists('resource', $data['overview'])) {
                $overviewResource = null;
                if (!empty($data['overview']['resource'])) {
                    $overviewResource = $this->resourceNodeRepo->findOneBy(['uuid' => $data['overview']['resource']['id']]);
                }

                $sequence->setOverviewResource($overviewResource);
            }
        }

        if (!empty($data['end'])) {
            if (!empty($data['end']['back'])) {
                $this->sipe('end.back.type', 'setEndBackType', $data, $sequence);
                $this->sipe('end.back.label', 'setEndBackLabel', $data, $sequence);

                if (array_key_exists('target', $data['end']['back'])) {
                    $targetResource = null;
                    if (!empty($data['end']['back']['target'])) {
                        $targetResource = $this->resourceNodeRepo->findOneBy(['uuid' => $data['end']['back']['target']['id']]);
                    }

                    $sequence->setEndBackTarget($targetResource);
                }
            }
        }

        if (isset($data['restrictions'])) {
            $this->sipe('restrictions.code', 'setAccessCode', $data, $sequence);

            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);
                $sequence->setAccessibleFrom($dateRange[0]);
                $sequence->setAccessibleUntil($dateRange[1]);
            }
        }

        if (array_key_exists('steps', $data)) {
            $this->deserializeSteps($data['steps'] ?? [], $sequence, $options);
        }

        if (array_key_exists('assignments', $data)) {
            $this->deserializeAssignments($data['assignments'] ?? [], $sequence, $options);
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($sequence, $data['tags'], $options);
        }

        return $sequence;
    }

    private function deserializeSteps(array $stepsData, Sequence $sequence, array $options = []): void
    {
        $ids = [];
        foreach ($stepsData as $stepIndex => $stepData) {
            $step = null;
            if ($stepData['id']) {
                $step = $sequence->getStep($stepData['id']);
            }

            if (empty($step)) {
                $step = new Step();
            }

            $step->setSequence($sequence);
            $step->setOrder($stepIndex);

            $this->stepSerializer->deserialize($step, $stepData, $options);
            $ids[] = $step->getUuid();
        }

        // removes steps which no longer exist
        $currentSteps = $sequence->getRootSteps();
        foreach ($currentSteps as $currentStep) {
            if (!in_array($currentStep->getUuid(), $ids)) {
                $currentStep->setSequence(null);
            }
        }
    }

    private function deserializeAssignments(array $assignmentsData, Sequence $sequence, array $options = []): void
    {
        $ids = [];
        foreach ($assignmentsData as $assignmentData) {
            $assignment = null;
            if (isset($assignmentData['id'])) {
                $assignment = $sequence->getAssignment($assignmentData['id']);
            }

            if (empty($assignment)) {
                $assignment = new Assignment();
            }

            $sequence->addAssignment($assignment);
            $this->assignmentSerializer->deserialize($assignmentData, $assignment, $options);

            $ids[] = $assignment->getUuid();
        }

        // removes assignments which no longer exist
        $currentAssignments = $sequence->getAssignments();
        foreach ($currentAssignments as $currentAssignment) {
            if (!in_array($currentAssignment->getUuid(), $ids)) {
                $sequence->removeAssignment($currentAssignment);
            }
        }
    }

    private function serializeTags(Sequence $sequence): array
    {
        $event = new GenericDataEvent([
            'class' => Sequence::class,
            'ids' => [$sequence->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function deserializeTags(Sequence $sequence, array $tags = [], array $options = []): void
    {
        if (in_array(Options::PERSIST_TAG, $options)) {
            $event = new GenericDataEvent([
                'tags' => $tags,
                'data' => [
                    [
                        'class' => Sequence::class,
                        'id' => $sequence->getUuid(),
                        'name' => $sequence->getName(),
                    ],
                ],
                'replace' => true,
            ]);

            $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
        }
    }
}
