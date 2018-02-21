<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/transfer")
 */
class TransferController
{
    /** @var TransferProvider */
    private $provider;

    /** @var FinderProvider */
    private $finder;

    /** @var SerializerProvider */
    private $serializer;

    /** @var string */
    private $schemaDir;

    /**
     * @DI\InjectParams({
     *    "provider"   = @DI\Inject("claroline.api.transfer"),
     *    "finder"     = @DI\Inject("claroline.api.finder"),
     *    "serializer" = @DI\Inject("claroline.api.serializer"),
     *    "schemaDir"  = @DI\Inject("%claroline.api.core_schema.dir%"),
     *    "fileUt"     = @DI\Inject("claroline.utilities.file")
     * })
     *
     * @param TransferProvider   $provider
     * @param FinderProvider     $finder
     * @param SerializerProvider $serializer
     * @param FileUtilities      $fileUt
     * @param string             $schemaDir
     */
    public function __construct(
        TransferProvider $provider,
        FinderProvider $finder,
        SerializerProvider $serializer,
        FileUtilities $fileUt,
        $schemaDir
    ) {
        $this->provider = $provider;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->schemaDir = $schemaDir;
        $this->fileUt = $fileUt;
    }

    /**
     * Difference with file controller ?
     *
     * @Route(
     *    "",
     *    name="apiv2_transfer_execute"
     * )
     * @Method("POST")
     *
     * @param Request $request
     */
    public function executeAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $publicFile = $this->serializer->deserialize(
          'Claroline\CoreBundle\Entity\File\PublicFile',
          $data['file']
        );

        $content = $this->fileUt->getContents($publicFile);

        $this->provider->execute(
            $content,
            $data['action'],
            $publicFile->getMimeType(),
            $this->getLogFile($request)
        );

        return new JsonResponse('done', 200);
    }

    /**
     * Difference with file controller ?
     *
     * @Route(
     *    "/schema",
     *    name="apiv2_transfer_schema"
     * )
     * @Method("GET")
     */
    public function schemaAction()
    {
        $file = $this->schemaDir.'/transfer.json';

        return new JsonResponse($this->serializer->loadSchema($file));
    }

    /**
     * @Route(
     *    "/export/{format}",
     *    name="apiv2_transfer_export"
     * )
     * @Method("GET")
     */
    public function exportAction(Request $request, $format)
    {
        $results = $this->finder->search(
            //maybe use a class map because it's the entity one currently
            $request->query->get('class'),
            $request->query->all(),
            []
        );

        return new Response($this->provider->format($format, $results['data'], $request->query->all()));
    }

    /**
     * @Route("/action/{name}/{format}", name="apiv2_transfer_action")
     * @Method("GET")
     */
    public function getAction($name, $format)
    {
        return new JsonResponse($this->provider->explainAction($name, $format));
    }

    /**
     * @Route("/actions/{format}", name="apiv2_transfer_actions")
     * @Method("GET")
     */
    public function getAvailableActions($format)
    {
        return new JsonResponse($this->provider->getAvailableActions($format));
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getLogFile(Request $request)
    {
        return $request->query->get('log');
    }
}
