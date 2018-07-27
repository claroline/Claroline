<?php

namespace Claroline\DropZoneBundle\Listener;

use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionDeleteEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionEndEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionStartEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Claroline\DropZoneBundle\Event\Log\LogCriterionCreateEvent;
use Claroline\DropZoneBundle\Event\Log\LogCriterionDeleteEvent;
use Claroline\DropZoneBundle\Event\Log\LogCriterionUpdateEvent;
use Claroline\DropZoneBundle\Event\Log\LogDocumentCreateEvent;
use Claroline\DropZoneBundle\Event\Log\LogDocumentDeleteEvent;
use Claroline\DropZoneBundle\Event\Log\LogDocumentOpenEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropEndEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropEvaluateEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropStartEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @DI\Service("claroline.listener.dropzone.badge_listener")
 */
class BadgeListener
{
    /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    private $router;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /**
     * @DI\InjectParams({
     *     "router"        = @DI\Inject("router"),
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(Router $router, EntityManager $entityManager)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    /**
     * @DI\Observe("badge-resource-claroline_dropzone-correction_delete-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-correction_end-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-correction_start-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-correction_update-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-correction_validation_change-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-criterion_create-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-criterion_delete-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-criterion_update-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-document_create-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-document_delete-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-document_open-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-drop_end-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-drop_evaluate-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-drop_start-generate_validation_link")
     * @DI\Observe("badge-resource-claroline_dropzone-dropzone_configure-generate_validation_link")
     */
    public function onBagdeCreateValidationLink($event)
    {
        $content = null;
        $log = $event->getLog();

        switch ($log->getAction()) {
            case LogCorrectionDeleteEvent::ACTION:
            case LogCorrectionEndEvent::ACTION:
            case LogCorrectionStartEvent::ACTION:
            case LogCorrectionUpdateEvent::ACTION:
            case LogCorrectionValidationChangeEvent::ACTION:
            case LogCriterionCreateEvent::ACTION:
            case LogCriterionDeleteEvent::ACTION:
            case LogCriterionUpdateEvent::ACTION:
            case LogDocumentCreateEvent::ACTION:
            case LogDocumentDeleteEvent::ACTION:
            case LogDocumentOpenEvent::ACTION:
            case LogDropEndEvent::ACTION:
            case LogDropEvaluateEvent::ACTION:
            case LogDropStartEvent::ACTION:
            case LogDropzoneConfigureEvent::ACTION:
                $logDetails = $event->getLog()->getDetails();
                $parameters = ['resourceId' => $logDetails['dropzone']['id']];
                $url = $this->router->generate('claroline_dropzone_open', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);

                /** @var Dropzone $dropzone */
                $dropzone = $this->entityManager->getRepository('ClarolineDropZoneBundle:Dropzone')->findOneById($logDetails['dropzone']['id']);
                $title = $dropzone->getResourceNode()->getName();
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
