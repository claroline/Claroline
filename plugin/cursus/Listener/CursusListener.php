<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CursusBundle\Manager\CourseManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CursusListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var CourseManager */
    private $cursusManager;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var ToolManager */
    private $toolManager;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param AuthorizationCheckerInterface $authorization
     * @param CourseManager                 $cursusManager
     * @param ParametersSerializer          $parametersSerializer
     * @param ToolManager                   $toolManager
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CourseManager $cursusManager,
        ParametersSerializer $parametersSerializer,
        ToolManager $toolManager,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->parametersSerializer = $parametersSerializer;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    /**
     * @param OpenToolEvent $event
     */
    public function onAdministrationToolOpen(OpenToolEvent $event)
    {
        $event->setData([
            'parameters' => $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]),
        ]);
        $event->stopPropagation();
    }

    /**
     * @param OpenToolEvent $event
     */
    public function onDisplayWorkspaceSessionEventTool(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }

    /**
     * @param OpenToolEvent $event
     */
    public function onDisplayDesktopCursusTool(OpenToolEvent $event)
    {
        $event->setData([
            'parameters' => $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]),
        ]);
        $event->stopPropagation();
    }

    /**
     * @param GenericDataEvent $event
     */
    public function onAgendaEventsRequest(GenericDataEvent $event)
    {
        $events = $event->getResponse() ? $event->getResponse() : [];
        $data = $event->getData();
        $type = $data['type'];
        $sessionEvents = [];

        if ('workspace' === $type) {
            $workspace = $data['workspace'];
            $sessionEvents = $this->cursusManager->getSessionEventsByWorkspace($workspace);
        } elseif ('desktop' === $type) {
            $sessionEvents = [];
        }
        foreach ($sessionEvents as $sessionEvent) {
            $sessionWorkspace = $sessionEvent->getSession()->getWorkspace();
            $description = $sessionEvent->getDescription() ? $sessionEvent->getDescription() : '';
            $location = $sessionEvent->getLocation();
            $locationExtra = $sessionEvent->getLocationExtra();
            $teachers = $sessionEvent->getTutors();

            if ($location || $locationExtra) {
                $description .= $description ?
                    '<hr/><b>'.$this->translator->trans('location', [], 'platform').'</b><br/>' :
                    '';
                if ($location) {
                    $description .= '<div>'.
                        $location->getName().
                        '<br/>'.
                        $location->getStreet().', '.$location->getStreetNumber();
                    $description .= $location->getBoxNumber() ? '/'.$location->getBoxNumber() : '';
                    $description .= '<br/>'.
                        $location->getPc().' '.$location->getTown().
                        '<br/>'.
                        $location->getCountry();
                    $description .= $location->getPhone() ? '<br/>'.$location->getPhone() : '';
                    $description .= '</div>';
                }
                $description .= $locationExtra ? $locationExtra : '';
            }
            if (count($teachers) > 0) {
                $description .= $description ? '<hr/>' : '';
                $description .= '<b>'.$this->translator->trans('tutors', [], 'cursus').'</b>'.
                    '<br/>'.
                    '<ul>';

                foreach ($teachers as $teacher) {
                    $description .= '<li>'.
                        $teacher->getFirstName().' '.$teacher->getLastName().
                        '</li>';
                }
                $description .= '</ul>';
            }
            $events[] = [
                'title' => $sessionEvent->getName(),
                'start' => $sessionEvent->getStartDate()->format(\DateTime::ISO8601),
                'end' => $sessionEvent->getEndDate()->format(\DateTime::ISO8601),
                'description' => $description,
                'color' => '#578E48',
                'allDay' => false,
                'isTask' => false,
                'isTaskDone' => false,
                'isEditable' => false,
                'workspace_id' => $sessionWorkspace ? $sessionWorkspace->getId() : null,
                'workspace_name' => $sessionWorkspace ? $sessionWorkspace->getName() : null,
                'className' => 'pointer-hand session_event_'.$sessionEvent->getId(),
                'durationEditable' => false,
            ];
        }
        $event->setResponse($events);
    }
}
