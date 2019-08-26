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

use Claroline\AppBundle\Api\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Import\File as HistoryFile;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.api_manager")
 * This service allows 2 instances of claroline-connect to communicate through their REST api.
 * The REST api requires an oauth authentication (wich is why the $id/$secret combination is required)
 */
class ApiManager
{
    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "curlManager"  = @DI\Inject("claroline.manager.curl_manager"),
     *     "container"    = @DI\Inject("service_container"),
     *     "transfer"     = @DI\Inject("claroline.api.transfer"),
     *     "finder"       =  @DI\Inject("claroline.api.finder"),
     *     "serializer"   =  @DI\Inject("claroline.api.serializer"),
     *     "fileUt"       = @DI\Inject("claroline.utilities.file"),
     *     "crud"         = @DI\Inject("claroline.api.crud"),
     * })
     */
    public function __construct(
        ObjectManager $om,
        CurlManager $curlManager,
        $container,
        $transfer,
        $finder,
        $serializer,
        $fileUt,
        $crud
    ) {
        $this->om = $om;
        $this->curlManager = $curlManager;
        $this->container = $container;
        $this->transfer = $transfer;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->fileUt = $fileUt;
        $this->crud = $crud;
    }

    public function import(PublicFile $publicFile, $action, $log, array $extra = [])
    {
        $historyFile = $this->finder->fetch(
            HistoryFile::class,
            ['file' => $publicFile->getId()]
        )[0];

        $this->crud->replace($historyFile, 'log', $log);
        $this->crud->replace($historyFile, 'executionDate', new \DateTime());
        //this is here otherwise the entity manager can crash and... well that's an issue.
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

        //should probably reset entity manager here
        if (0 === count($data['data']['error'])) {
            $this->crud->replace($historyFile, 'status', HistoryFile::STATUS_SUCCESS);
        }
    }
}
