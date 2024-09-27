<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\OrganizationSerializer;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BadgeClassSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly WorkspaceSerializer $workspaceSerializer,
        private readonly OrganizationSerializer $organizationSerializer,
        private readonly RuleSerializer $ruleSerializer,
        private readonly TemplateSerializer $templateSerializer
    ) {
    }

    public function getName(): string
    {
        return 'open_badge_badge';
    }

    public function getClass(): string
    {
        return BadgeClass::class;
    }

    public function getSchema(): string
    {
        return '#/plugin/open-badge/badge.json';
    }

    public function serialize(BadgeClass $badge, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $badge->getUuid(),
                'name' => $badge->getName(),
                'description' => $badge->getDescription(),
                'image' => $badge->getImage(),
                'color' => $badge->getColor(),
            ];
        }

        $data = [
            'id' => $badge->getUuid(),
            'name' => $badge->getName(),
            'description' => $badge->getDescription(),
            'image' => $badge->getImage(),
            'color' => $badge->getColor(),
            'criteria' => $badge->getCriteria(),
            'duration' => $badge->getDurationValidation(),
            'tags' => $this->serializeTags($badge),
            'meta' => [
                'createdAt' => DateNormalizer::normalize($badge->getCreatedAt()),
                'updatedAt' => DateNormalizer::normalize($badge->getUpdatedAt()),
                'archived' => $badge->isArchived(),
            ],
            'workspace' => $badge->getWorkspace() ? $this->workspaceSerializer->serialize($badge->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $data['permissions'] = $this->serializePermissions($badge);
        }

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $data['issuer'] = $badge->getIssuer() ? $this->organizationSerializer->serialize($badge->getIssuer(), [SerializerInterface::SERIALIZE_MINIMAL]) : null;
            $data['notifyGrant'] = $badge->getNotifyGrant();
            $data['issuingPeer'] = $badge->hasIssuingPeer();
            $data['restrictions'] = [
                'hideRecipients' => $badge->getHideRecipients(),
            ];
            $data['rules'] = array_map(function (Rule $rule) {
                return $this->ruleSerializer->serialize($rule);
            }, $badge->getRules()->toArray());

            if ($badge->getTemplate()) {
                $data['template'] = $this->templateSerializer->serialize($badge->getTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        return $data;
    }

    public function deserialize(array $data, BadgeClass $badge = null, array $options = []): BadgeClass
    {
        $this->sipe('name', 'setName', $data, $badge);
        $this->sipe('description', 'setDescription', $data, $badge);
        $this->sipe('image', 'setImage', $data, $badge);
        $this->sipe('color', 'setColor', $data, $badge);
        $this->sipe('color', 'setColor', $data, $badge);
        $this->sipe('criteria', 'setCriteria', $data, $badge);
        $this->sipe('duration', 'setDurationValidation', $data, $badge);
        $this->sipe('issuingPeer', 'setIssuingPeer', $data, $badge);
        $this->sipe('notifyGrant', 'setNotifyGrant', $data, $badge);
        $this->sipe('restrictions.hideRecipients', 'setHideRecipients', $data, $badge);

        if (isset($data['meta'])) {
            $this->sipe('meta.archived', 'setArchived', $data, $badge);

            if (isset($data['meta']['createdAt'])) {
                $badge->setCreatedAt(DateNormalizer::denormalize($data['meta']['createdAt']));
            }

            if (isset($data['meta']['updatedAt'])) {
                $badge->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updatedAt']));
            }
        }

        if (isset($data['issuer'])) {
            /** @var Organization $organization */
            $organization = $this->om->getObject($data['issuer'], Organization::class);
            $badge->setIssuer($organization);
        }

        if (array_key_exists('workspace', $data)) {
            $workspace = null;
            if (!empty($data['workspace']) && !empty($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
            }

            $badge->setWorkspace($workspace);
        }

        if (array_key_exists('template', $data)) {
            $template = null;
            if (!empty($data['template']) && !empty($data['template']['id'])) {
                /** @var Template $template */
                $template = $this->om->getRepository(Template::class)->findOneBy(['uuid' => $data['template']['id']]);
            }

            $badge->setTemplate($template);
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($badge, $data['tags']);
        }

        if (isset($data['rules'])) {
            $this->deserializeRules($data['rules'], $badge);
        }

        return $badge;
    }

    private function deserializeRules(array $rules, BadgeClass $badge): void
    {
        /** @var Rule[] $existingRules */
        $existingRules = $badge->getRules();

        $ids = [];
        foreach ($rules as $ruleData) {
            $existingRule = null;
            if (isset($ruleData['id'])) {
                foreach ($existingRules as $rule) {
                    if ($rule->getUuid() === $ruleData['id']) {
                        $existingRule = $rule;
                        break;
                    }
                }
            }

            if (empty($existingRule)) {
                $existingRule = new Rule();
            }

            $rule = $this->ruleSerializer->deserialize($ruleData, $existingRule);
            $badge->addRule($rule);

            $ids[] = $rule->getUuid();
        }

        // removes rules which no longer exists
        foreach ($existingRules as $rule) {
            if (!in_array($rule->getUuid(), $ids)) {
                $badge->removeRule($rule);
            }
        }
    }

    private function deserializeTags(BadgeClass $badge, array $tags = []): void
    {
        $event = new GenericDataEvent([
            'tags' => $tags,
            'data' => [
                [
                    'class' => BadgeClass::class,
                    'id' => $badge->getUuid(),
                    'name' => $badge->getName(),
                ],
            ],
            'replace' => true,
        ]);

        $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
    }

    private function serializeTags(BadgeClass $badge): array
    {
        $event = new GenericDataEvent([
            'class' => BadgeClass::class,
            'ids' => [$badge->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function serializePermissions(BadgeClass $badge): array
    {
        $administrate = $this->authorization->isGranted('ADMINISTRATE', $badge);

        return [
            'open' => $administrate || $this->authorization->isGranted('OPEN', $badge),
            'grant' => $administrate || $this->authorization->isGranted('GRANT', $badge),
            'edit' => $administrate || $this->authorization->isGranted('EDIT', $badge),
            'administrate' => $administrate || $this->authorization->isGranted('ADMINISTRATE', $badge),
            'delete' => $administrate || $this->authorization->isGranted('DELETE', $badge),
        ];
    }
}
