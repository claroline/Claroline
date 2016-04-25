<?php

namespace Icap\DropzoneBundle\Listener;

use Doctrine\ORM\EntityManager;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogCorrectionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionEndEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionStartEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogCriterionUpdateEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentOpenEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEndEvent;
use Icap\DropzoneBundle\Event\Log\LogDropEvaluateEvent;
use Icap\DropzoneBundle\Event\Log\LogDropStartEvent;
use Icap\DropzoneBundle\Event\Log\LogDropzoneConfigureEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.listener.dropzone.badge_listener")
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
     * @DI\Observe("badge-resource-icap_dropzone-correction_delete-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-correction_end-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-correction_start-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-correction_update-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-correction_validation_change-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-criterion_create-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-criterion_delete-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-criterion_update-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-document_create-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-document_delete-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-document_open-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-drop_end-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-drop_evaluate-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-drop_start-generate_validation_link")
     * @DI\Observe("badge-resource-icap_dropzone-dropzone_configure-generate_validation_link")
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
                $parameters = array('resourceId' => $logDetails['dropzone']['id']);
                $url = $this->router->generate('icap_dropzone_open', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);

                /** @var Dropzone $dropzone */
                $dropzone = $this->entityManager->getRepository('IcapDropzoneBundle:Dropzone')->findOneById($logDetails['dropzone']['id']);
                $title = $dropzone->getResourceNode()->getName();
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
