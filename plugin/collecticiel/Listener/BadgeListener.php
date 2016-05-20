<?php

namespace Innova\CollecticielBundle\Listener;

use Claroline\CoreBundle\Event\Badge\BadgeCreateValidationLinkEvent;
use Doctrine\ORM\EntityManager;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogCorrectionDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionEndEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionStartEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionUpdateEvent;
use Innova\CollecticielBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogCriterionUpdateEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentOpenEvent;
use Innova\CollecticielBundle\Event\Log\LogDropEndEvent;
use Innova\CollecticielBundle\Event\Log\LogDropEvaluateEvent;
use Innova\CollecticielBundle\Event\Log\LogDropStartEvent;
use Innova\CollecticielBundle\Event\Log\LogDropzoneConfigureEvent;
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
     * @DI\Observe("badge-resource-innova_collecticiel-correction_delete-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-correction_end-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-correction_start-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-correction_update-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-correction_validation_change-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-criterion_create-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-criterion_delete-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-criterion_update-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-document_create-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-document_delete-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-document_open-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-drop_end-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-drop_evaluate-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-drop_start-generate_validation_link")
     * @DI\Observe("badge-resource-innova_collecticiel-dropzone_configure-generate_validation_link")
     */
    public function onBagdeCreateValidationLink(BadgeCreateValidationLinkEvent $event)
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
                $url = $this->router->generate('innova_collecticiel_open', $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);

                /** @var Dropzone $dropzone */
                $dropzone = $this->entityManager->getRepository('InnovaCollecticielBundle:Dropzone')->findOneById($logDetails['dropzone']['id']);
                $title = $dropzone->getResourceNode()->getName();
                $content = sprintf('<a href="%s" title="%s">%s</a>', $url, $title, $title);
                break;
        }

        $event->setContent($content);
        $event->stopPropagation();
    }
}
