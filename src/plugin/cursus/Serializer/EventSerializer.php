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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Planning\PlannedObjectSerializer;
use Claroline\CoreBundle\API\Serializer\Template\TemplateSerializer;
use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\EventRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var PlannedObjectSerializer */
    private $plannedObjectSerializer;
    /** @var SessionSerializer */
    private $sessionSerializer;
    /** @var TemplateSerializer */
    private $templateSerializer;

    /** @var ObjectRepository */
    private $sessionRepo;
    /** @var EventRepository */
    private $eventRepo;
    /** @var ObjectRepository */
    private $templateRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PlannedObjectSerializer $plannedObjectSerializer,
        SessionSerializer $sessionSerializer,
        TemplateSerializer $templateSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->plannedObjectSerializer = $plannedObjectSerializer;
        $this->sessionSerializer = $sessionSerializer;
        $this->templateSerializer = $templateSerializer;

        $this->sessionRepo = $om->getRepository(Session::class);
        $this->eventRepo = $om->getRepository(Event::class);
        $this->templateRepo = $om->getRepository(Template::class);
    }

    public function serialize(Event $event, array $options = []): array
    {
        $serialized = array_merge_recursive($this->plannedObjectSerializer->serialize($event->getPlannedObject(), $options), [
            'code' => $event->getCode(),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $event),
                'edit' => $this->authorization->isGranted('EDIT', $event),
                'delete' => $this->authorization->isGranted('DELETE', $event),
            ],
            'session' => $event->getSession() ? $this->sessionSerializer->serialize($event->getSession(), [Options::SERIALIZE_MINIMAL]) : null,
        ]);

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'restrictions' => [
                    'users' => $event->getMaxUsers(),
                ],
                'participants' => $this->eventRepo->countParticipants($event),
                'registration' => [
                    'registrationType' => $event->getRegistrationType(),
                ],
                'presenceTemplate' => $event->getPresenceTemplate() ?
                    $this->templateSerializer->serialize($event->getPresenceTemplate(), [Options::SERIALIZE_MINIMAL]) :
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

        $session = $event->getSession();
        if (empty($session) && isset($data['session']['id'])) {
            /** @var Session $session */
            $session = $this->sessionRepo->findOneBy(['uuid' => $data['session']['id']]);

            if ($session) {
                $event->setSession($session);
            }
        }

        if (isset($data['presenceTemplate'])) {
            $template = null;
            if (!empty($data['presenceTemplate']) && $data['presenceTemplate']['id']) {
                $template = $this->templateRepo->findOneBy(['uuid' => $data['presenceTemplate']['id']]);
            }

            $event->setPresenceTemplate($template);
        }

        return $event;
    }
}
