<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Api\Options;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Import\File as HistoryFile;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;

class ApiManager
{
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var TransferProvider */
    private $transfer;
    /** @var FileUtilities */
    private $fileUt;

    /**
     * ApiManager constructor.
     *
     * @param ObjectManager    $om
     * @param TransferProvider $transfer
     * @param FileUtilities    $fileUt
     * @param Crud             $crud
     */
    public function __construct(
        ObjectManager $om,
        TransferProvider $transfer,
        FileUtilities $fileUt,
        Crud $crud
    ) {
        $this->om = $om;
        $this->transfer = $transfer;
        $this->fileUt = $fileUt;
        $this->crud = $crud;
    }

    public function import(PublicFile $publicFile, $action, $log, array $extra = [])
    {
        $historyFile = $this->om->getRepository(HistoryFile::class)->findOneBy(['file' => $publicFile->getId()]);

        $this->crud->replace($historyFile, 'log', $log);
        $this->crud->replace($historyFile, 'executionDate', new \DateTime());
        // this is here otherwise the entity manager can crash and... well that's an issue.
        $this->crud->replace($historyFile, 'status', HistoryFile::STATUS_ERROR);

        $content = $this->fileUt->getContents($publicFile);
        $options = [];

        if (isset($extra['workspace'])) {
            $options[] = Options::WORKSPACE_IMPORT;
        }

        $data = $this->transfer->execute(
            $content,
            $action,
            $publicFile->getMimeType(),
            $log,
            $options,
            $extra
        );

        // should probably reset entity manager here
        if (0 === count($data['data']['error'])) {
            $this->crud->replace($historyFile, 'status', HistoryFile::STATUS_SUCCESS);
        }
    }
}
