<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Planning\PlannedObjectSerializer;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var PlannedObjectSerializer */
    private $plannedObjectSerializer;
    /** @var TemplateSerializer */
    private $templateSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        WorkspaceSerializer $workspaceSerializer,
        PlannedObjectSerializer $plannedObjectSerializer,
        TemplateSerializer $templateSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->plannedObjectSerializer = $plannedObjectSerializer;
        $this->templateSerializer = $templateSerializer;
    }

    public function getName()
    {
        return 'event';
    }

    public function serialize(Event $event, array $options = []): array
    {
        return array_merge_recursive($this->plannedObjectSerializer->serialize($event->getPlannedObject(), $options), [
            'workspace' => $event->getWorkspace() ? $this->workspaceSerializer->serialize($event->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null,
            'permissions' => [
                'edit' => $this->authorization->isGranted('EDIT', $event),
                'delete' => $this->authorization->isGranted('DELETE', $event),
            ],
            'invitationTemplate' => $event->getInvitationTemplate() ?
                $this->templateSerializer->serialize($event->getInvitationTemplate(), [Options::SERIALIZE_MINIMAL]) :
                null,
        ]);
    }

    public function deserialize(array $data, Event $event): Event
    {
        $this->plannedObjectSerializer->deserialize($data, $event->getPlannedObject());

        $this->sipe('id', 'setUuid', $data, $event);

        if (isset($data['workspace'])) {
            $workspace = null;
            if (isset($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->om->getObject($data['workspace'], Workspace::class);
            }

            $event->setWorkspace($workspace);
        }

        if (isset($data['invitationTemplate'])) {
            $template = null;
            if (!empty($data['invitationTemplate']) && $data['invitationTemplate']['id']) {
                /** @var Template $template */
                $template = $this->om->getObject($data['invitationTemplate'], Template::class);
            }

            $event->setInvitationTemplate($template);
        }

        return $event;
    }
}
