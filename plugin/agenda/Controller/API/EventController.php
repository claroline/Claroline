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
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/event")
 */
class EventController extends AbstractCrudController
{
    public function getClass()
    {
        return Event::class;
    }

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
     * @param Request $request
     *
     * @return StreamedResponse
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

        $workspace = $this->om->getRepository(Workspace::class)->find($id);
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function importAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $file = $data['file'];
        $workspace = $data['workspace'];
        $workspace = $workspace['id'] ? $this->om->getObject($workspace, Workspace::class) : null;
        $fileEntity = $this->om->getObject($file, PublicFile::class) ?? new PublicFile();
        $file = $this->serializer->deserialize($file, $fileEntity);
        $fileData = $this->container->get('claroline.utilities.file')->getContents($file);
        $events = $this->container->get('claroline.manager.agenda_manager')->import($fileData, $workspace);

        return new JsonResponse(array_map(function (Event $event) {
            return $this->serializer->serialize($event);
        }, $events));
    }
}
