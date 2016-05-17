<?php

namespace Innova\CollecticielBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;
use Innova\CollecticielBundle\Entity\ReturnReceipt;
use Innova\CollecticielBundle\Entity\ReturnReceiptType;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Entity\Document;

/**
 * @DI\Service("innova.manager.returnreceipt_manager")
 */
class ReturnReceiptManager
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    public function create(Document $document, User $user, Dropzone $dropzone, ReturnReceiptType $returnReceiptType)
    {
        $returnReceipt = new ReturnReceipt();
        $returnReceipt->setDocument($document);
        $returnReceipt->setUser($user);
        $returnReceipt->setDropzone($dropzone);
        $returnReceipt->setReturnReceiptType($returnReceiptType);

        $this->em->persist($returnReceipt);

        return $returnReceipt;
    }
}
