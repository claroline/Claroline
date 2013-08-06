<?php

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Form\AnnouncementType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class AnnouncementController extends Controller
{
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "formFactory" = @DI\Inject("form.factory")
     * })
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @EXT\Route(
     *     "/open/aggregate/{aggregateId}",
     *     name = "claro_announcement_aggregate_open"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::announcementsList.html.twig")
     *
     * @param AnnouncementAggregate $aggregate
     *
     * @return Response
     */
    public function openAction(AnnouncementAggregate $aggregate)
    {
        return array(
            '_resource' => $aggregate,
            'announcements' => $aggregate->getAnnouncements()
        );
    }

    /**
     * @EXT\Route(
     *     "/aggregate/{aggregateId}/create/form",
     *     name = "claro_announcement_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::createForm.html.twig")
     *
     * @param AnnouncementAggregate $aggregate
     *
     * @return Response
     */
    public function createFormAction(AnnouncementAggregate $aggregate)
    {
        $form = $this->formFactory->create(new AnnouncementType(), new Announcement());

        return array(
            'form' => $form->createView(),
            '_resource' => $aggregate
        );
    }

    /**
     * @EXT\Route(
     *     "/aggregate/{aggregateId}/create",
     *     name = "claro_announcement_create"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "aggregate",
     *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
     *      options={"id" = "aggregateId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineAnnouncementBundle::createForm.html.twig")
     *
     * @param AnnouncementAggregate $aggregate
     *
     * @return Response
     */
    public function createAction(AnnouncementAggregate $aggregate)
    {
        $form = $this->formFactory->create(new AnnouncementType(), new Announcement());

        return array(
            'form' => $form->createView(),
            '_resource' => $aggregate
        );
    }
}