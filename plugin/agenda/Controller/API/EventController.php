<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Controller\API;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @ApiMeta(class="Claroline\AgendaBundle\Entity\Event")
 * @Route("/event")
 */
class EventController extends AbstractCrudController
{
    public function getName()
    {
        return 'event';
    }

    /**
     * tweaked for fullcalendar.
     *
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function listAction(Request $request, $class)
    {
        $query = $request->query->all();
        $hiddenFilters = isset($query['hiddenFilters']) ? $query['hiddenFilters'] : [];
        $query['hiddenFilters'] = array_merge($hiddenFilters, $this->getDefaultHiddenFilters());

        //get start & end date and add them to the hidden filters list
        $query['hiddenFilters']['createdAfter'] = $query['start'];

        //we want to be able to fetch events that start a months before and ends a month after
        $date = new \DateTime($query['end']);
        $interval = new \DateInterval('P2M');
        $date->add($interval);
        $end = $date->format('Y-m-d');

        $query['hiddenFilters']['endBefore'] = $end;

        $data = $this->finder->search(
            $class,
            $query,
            $this->options['list']
        );

        return new JsonResponse($data['data']);
    }

    /**
     * @EXT\Route(
     *     "/download",
     *     name="apiv2_download_agenda"
     * )
     * @EXT\Method("GET")
     *
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction(Request $request)
    {
        $id = $request->query->get('workspace');
        $file = $this->container->get('claroline.manager.agenda_manager')->export($id);

        $response = new StreamedResponse();

        $response->setCallBack(
          function () use ($file) {
              readfile($file);
          }
        );

        $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find($id);
        $name = $workspace ? $workspace->getName() : 'desktop';
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$name.'.ics');
        $response->headers->set('Content-Type', ' text/calendar');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/import",
     *     name="apiv2_event_import"
     * )
     * @EXT\Method("POST")
     *
     * @param Workspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function importAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $file = $data['file'];
        $workspace = $data['workspace'];
        $workspace = $workspace['id'] ?
            $this->serializer->deserialize('Claroline\CoreBundle\Entity\Workspace\Workspace', $workspace) :
            null;
        $file = $this->serializer->deserialize('Claroline\CoreBundle\Entity\File\PublicFile', $file);
        $fileData = $this->container->get('claroline.utilities.file')->getContents($file);
        $events = $this->container->get('claroline.manager.agenda_manager')->import($fileData, $workspace);

        return new JsonResponse(array_map(function (Event $event) {
            return $this->serializer->serialize($event);
        }, $events));
    }
}
