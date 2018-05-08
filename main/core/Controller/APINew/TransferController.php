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

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/transfer")
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Import\File", ignore={"update", "exist", "schema"})
 */
class TransferController extends AbstractCrudController
{
    /** @var TransferProvider */
    private $provider;

    /** @var Crud */
    protected $crud;

    /** @var FinderProvider */
    protected $finder;

    /** @var SerializerProvider */
    protected $serializer;

    /** @var string */
    private $schemaDir;

    /**
     * @DI\InjectParams({
     *    "provider"   = @DI\Inject("claroline.api.transfer"),
     *    "router"     = @DI\Inject("router"),
     *    "schemaDir"  = @DI\Inject("%claroline.api.core_schema.dir%"),
     *    "fileUt"     = @DI\Inject("claroline.utilities.file"),
     *    "crud"       = @DI\Inject("claroline.api.crud"),
     *    "manager"    = @DI\Inject("claroline.manager.api_manager"),
     *    "async"      = @DI\Inject("claroline.async.command")
     * })
     *
     * @param TransferProvider $provider
     * @param FileUtilities    $fileUt
     * @param string           $schemaDir
     */
    public function __construct(
        TransferProvider $provider,
        FileUtilities $fileUt,
        RouterInterface $router,
        Crud $crud,
        $schemaDir,
        $async
    ) {
        $this->provider = $provider;
        $this->schemaDir = $schemaDir;
        $this->fileUt = $fileUt;
        $this->router = $router;
        $this->crud = $crud;
        $this->async = $async;
    }

    /**
     * @Route(
     *    "/upload",
     *    name="apiv2_transfer_upload_file"
     * )
     * @Method("POST")
     *
     * @param Request $request
     */
    public function uploadFileAction(Request $request)
    {
        $file = $this->uploadFile($request);

        $this->crud->create(
            'Claroline\CoreBundle\Entity\Import\File',
            ['uploadedFile' => $file]
        );

        return new JsonResponse([$file], 200);
    }

    public function getName()
    {
        return 'transfer';
    }

    /**
     * @Route(
     *    "/start",
     *    name="apiv2_transfer_start"
     * )
     * @Method("POST")
     *
     * @param Request $request
     */
    public function startAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $publicFile = $this->serializer->deserialize(
            'Claroline\CoreBundle\Entity\File\PublicFile',
            $data['file']
        );

        $this->container->get('claroline.manager.api_manager')->import(
            $publicFile,
            $data['action'],
            $this->getLogFile($request)
        );

        //the following line doesn't work on our live server but it's supposed to be the proper way to do it
        //$this->async->run('claroline:api:load '.$publicFile->getId().' '.$data['action'].' '.$this->getLogFile($request));

        return new JsonResponse('started', 200);
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
    public function schemaAction($class)
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
    public function getExplanationAction($name, $format)
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

    public function uploadFile(Request $request)
    {
        $file = $request->files->all()['file'];
        $handler = $request->get('handler');

        /** @var StrictDispatcher */
        $dispatcher = $this->container->get('claroline.event.event_dispatcher');

        $object = $this->crud->create(
              'Claroline\CoreBundle\Entity\File\PublicFile',
              [],
              ['file' => $file]
          );

        $dispatcher->dispatch(strtolower('upload_file_'.$handler), 'UploadFile', [$object]);

        return $this->serializer->serialize($object);
    }

    /**
     * @return array
     *               It would be nice to automatize this
     */
    protected function getRequirements()
    {
        return [
            'get' => ['id' => '^(?!.*(schema|copy|parameters|find|transfer|\/)).*'],
            'update' => ['id' => '^(?!.*(schema|parameters|find|transfer|\/)).*'],
            'exist' => [],
        ];
    }
}
