<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Options as APIOptions;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\OrganizationSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\Organization\OrganizationManager;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BadgeClassSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    private $router;
    private $fileUt;
    private $workspaceSerializer;
    private $om;
    private $organizationManager;
    private $criteriaSerializer;
    private $imageSerializer;
    private $eventDispatcher;
    private $publicFileSerializer;
    private $organizationSerializer;
    private $ruleSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FileUtilities $fileUt,
        RouterInterface $router,
        ObjectManager $om,
        OrganizationManager $organizationManager,
        CriteriaSerializer $criteriaSerializer,
        EventDispatcherInterface $eventDispatcher,
        WorkspaceSerializer $workspaceSerializer,
        ImageSerializer $imageSerializer,
        OrganizationSerializer $organizationSerializer,
        PublicFileSerializer $publicFileSerializer,
        RuleSerializer $ruleSerializer
    ) {
        $this->authorization = $authorization;
        $this->router = $router;
        $this->fileUt = $fileUt;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->om = $om;
        $this->organizationManager = $organizationManager;
        $this->criteriaSerializer = $criteriaSerializer;
        $this->imageSerializer = $imageSerializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->publicFileSerializer = $publicFileSerializer;
        $this->organizationSerializer = $organizationSerializer;
        $this->ruleSerializer = $ruleSerializer;
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
        $image = null;
        if ($badge->getImage()) {
            /** @var PublicFile $image */
            $image = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $badge->getImage(),
            ]);
        }

        $data = [
            'id' => $badge->getUuid(),
            'name' => $badge->getName(),
            'description' => $badge->getDescription(),
            'color' => $badge->getColor(),
            'criteria' => $badge->getCriteria(),
            'duration' => $badge->getDurationValidation(),
            'image' => $image ? $this->publicFileSerializer->serialize($image) : null,
            'issuer' => $this->organizationSerializer->serialize($badge->getIssuer() ? $badge->getIssuer() : $this->organizationManager->getDefault(true)),
            'tags' => $this->serializeTags($badge),
        ];

        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            $data['id'] = $this->router->generate('apiv2_open_badge__badge_class', ['badge' => $badge->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
            $data['type'] = 'BadgeClass';
            $data['criteria'] = $this->criteriaSerializer->serialize($badge)['id'];
            $image = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $badge->getImage()]);

            if ($image) {
                //wtf, this is for mozilla backpack
                $data['image'] = $this->imageSerializer->serialize($image)['id'];
            }
        } else {
            $data['issuingPeer'] = $badge->hasIssuingPeer();
            $data['meta'] = [
                'created' => DateNormalizer::normalize($badge->getCreated()),
                'updated' => DateNormalizer::normalize($badge->getUpdated()),
                'enabled' => $badge->getEnabled(),
            ];
            $data['restrictions'] = [
                'hideRecipients' => $badge->getHideRecipients(),
            ];
            $data['permissions'] = $this->serializePermissions($badge);
            $data['rules'] = array_map(function (Rule $rule) {
                return $this->ruleSerializer->serialize($rule);
            }, $badge->getRules()->toArray());
            $data['workspace'] = $badge->getWorkspace() ? $this->workspaceSerializer->serialize($badge->getWorkspace(), [APIOptions::SERIALIZE_MINIMAL]) : null;
        }

        return $data;
    }

    public function deserialize(array $data, BadgeClass $badge = null, array $options = []): BadgeClass
    {
        $this->sipe('name', 'setName', $data, $badge);
        $this->sipe('description', 'setDescription', $data, $badge);
        $this->sipe('color', 'setColor', $data, $badge);
        $this->sipe('criteria', 'setCriteria', $data, $badge);
        $this->sipe('duration', 'setDurationValidation', $data, $badge);
        $this->sipe('issuingPeer', 'setIssuingPeer', $data, $badge);
        $this->sipe('meta.enabled', 'setEnabled', $data, $badge);
        $this->sipe('restrictions.hideRecipients', 'setHideRecipients', $data, $badge);

        if (isset($data['issuer'])) {
            /** @var Organization $organization */
            $organization = $this->om->getObject($data['issuer'], Organization::class);
            $badge->setIssuer($organization);
        }

        if (isset($data['image']) && isset($data['image']['id'])) {
            /** @var PublicFile $thumbnail */
            $thumbnail = $this->om->getObject($data['image'], PublicFile::class);
            $badge->setImage($data['image']['url']);
            $this->fileUt->createFileUse(
                $thumbnail,
                BadgeClass::class,
                $badge->getUuid()
            );
        }

        if (isset($data['workspace'])) {
            if (isset($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $data['workspace']['id']]);
                if ($workspace) {
                    $badge->setWorkspace($workspace);
                    // main organization maybe instead ? this is fishy
                    if (count($workspace->getOrganizations()) > 1) {
                        $badge->setIssuer($workspace->getOrganizations()[0]);
                    }
                }
            } else {
                $badge->setWorkspace(null);
            }
        }

        if (isset($data['tags'])) {
            $this->deserializeTags($badge, $data['tags']);
        }

        if (isset($data['rules'])) {
            $this->deserializeRules($data['rules'], $badge);
        }

        return $badge;
    }

    private function deserializeRules(array $rules, BadgeClass $badge)
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

    private function deserializeTags(BadgeClass $badge, array $tags = [])
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

    private function serializeTags(BadgeClass $badge)
    {
        $event = new GenericDataEvent([
            'class' => BadgeClass::class,
            'ids' => [$badge->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    private function serializePermissions(BadgeClass $badge)
    {
        return [
            'grant' => $this->authorization->isGranted('GRANT', $badge),
            'edit' => $this->authorization->isGranted('EDIT', $badge),
            'administrate' => $this->authorization->isGranted('ADMINISTRATE', $badge),
            'delete' => $this->authorization->isGranted('DELETE', $badge),
        ];
    }
}
