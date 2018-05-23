<?php

namespace Claroline\PdfGeneratorBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\PdfGeneratorBundle\Manager\PdfManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var PdfManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.pdf_manager")
     * })
     */
    public function __construct(PdfManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Pdf nodes
        $pdfCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolinePdfGeneratorBundle] updated Pdf count: $pdfCount");
    }
}
