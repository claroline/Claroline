<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Planning\PlannedObjectSerializer;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\EventRepository;
use Claroline\CursusBundle\Repository\SessionRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventSerializer
{
    use SerializerTrait;

    private SessionRepository $sessionRepo;
    private EventRepository $eventRepo;
    private ObjectRepository $templateRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly PlannedObjectSerializer $plannedObjectSerializer,
        private readonly UserSerializer $userSerializer,
        private readonly SessionSerializer $sessionSerializer,
        private readonly TemplateSerializer $templateSerializer
    ) {
        $this->sessionRepo = $om->getRepository(Session::class);
        $this->eventRepo = $om->getRepository(Event::class);
        $this->templateRepo = $om->getRepository(Template::class);
    }

    public function getClass(): string
    {
        return Event::class;
    }

    public function getSchema(): string
    {
        return '#/plugin/cursus/session-event.json';
    }

    public function serialize(Event $event, array $options = []): array
    {
        $serialized = array_merge_recursive($this->plannedObjectSerializer->serialize($event->getPlannedObject(), $options), [
            'code' => $event->getCode(),
            'session' => $event->getSession() ? $this->sessionSerializer->serialize($event->getSession(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ]);

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $serialized['permissions'] = [
                    'open' => $this->authorization->isGranted('OPEN', $event),
                    'edit' => $this->authorization->isGranted('EDIT', $event),
                    'delete' => $this->authorization->isGranted('DELETE', $event),
                    'register' => $this->authorization->isGranted('REGISTER', $event),
                ];
            }

            $tutors = $this->om->getRepository(EventUser::class)->findBy([
                'event' => $event,
                'type' => AbstractRegistration::TUTOR,
                'validated' => true,
                'confirmed' => true,
            ]);

            $serialized = array_merge($serialized, [
                'restrictions' => [
                    'users' => $event->getMaxUsers(),
                ],
                'participants' => $this->eventRepo->countParticipants($event),
                'tutors' => array_map(function (EventUser $eventUser) {
                    return $this->userSerializer->serialize($eventUser->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
                }, $tutors),
                'registration' => [
                    'registrationType' => $event->getRegistrationType(),
                    'mail' => $event->getRegistrationMail(),
                ],
                'presenceTemplate' => $event->getPresenceTemplate() ?
                    $this->templateSerializer->serialize($event->getPresenceTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
                'invitationTemplate' => $event->getInvitationTemplate() ?
                    $this->templateSerializer->serialize($event->getInvitationTemplate(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                    null,
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Event $event): Event
    {
        $this->plannedObjectSerializer->deserialize($data, $event->getPlannedObject());

        $this->sipe('id', 'setUuid', $data, $event);
        $this->sipe('code', 'setCode', $data, $event);
        $this->sipe('restrictions.users', 'setMaxUsers', $data, $event);
        $this->sipe('registration.registrationType', 'setRegistrationType', $data, $event);
        $this->sipe('registration.mail', 'setRegistrationMail', $data, $event);

        $session = $event->getSession();
        if (empty($session) && isset($data['session']['id'])) {
            /** @var Session $session */
            $session = $this->sessionRepo->findOneBy(['uuid' => $data['session']['id']]);

            if ($session) {
                $event->setSession($session);
            }
        }

        $template = null;
        if (!empty($data['presenceTemplate']) && $data['presenceTemplate']['id']) {
            $template = $this->templateRepo->findOneBy(['uuid' => $data['presenceTemplate']['id']]);
        }
        $event->setPresenceTemplate($template);

        $template = null;
        if (!empty($data['invitationTemplate']) && $data['invitationTemplate']['id']) {
            $template = $this->templateRepo->findOneBy(['uuid' => $data['invitationTemplate']['id']]);
        }
        $event->setInvitationTemplate($template);

        return $event;
    }
}
