<?php

namespace Innova\CollecticielBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Innova\CollecticielBundle\Repository\DocumentRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.manager.document_manager")
 */
class DocumentManager
{
    /** @var ObjectManager */
    private $om;

    /** @var DocumentRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->repo = $om->getRepository('InnovaCollecticielBundle:Document');
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $documents = $this->repo->findBySender($from);

        if (count($documents) > 0) {
            foreach ($documents as $document) {
                $document->setSender($to);
            }

            $this->om->flush();
        }

        return count($documents);
    }
}
