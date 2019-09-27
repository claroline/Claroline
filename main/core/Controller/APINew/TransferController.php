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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\TransferProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Import\File;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/transfer")
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
     * @param TransferProvider $provider
     * @param FileUtilities    $fileUt
     * @param string           $schemaDir
     */
    public function __construct(
        TransferProvider $provider,
        SerializerProvider $serializer,
        RouterInterface $router,
        $schemaDir,
        FileUtilities $fileUt,
        Crud $crud,
        ObjectManager $om,
        $async
    ) {
        $this->provider = $provider;
        $this->schemaDir = $schemaDir;
        $this->fileUt = $fileUt;
        $this->router = $router;
        $this->crud = $crud;
        $this->serializer = $serializer;
        $this->async = $async;
        $this->om = $om;
    }

    public function getClass()
    {
        return File::class;
    }

    public function getIgnore()
    {
        return ['update', 'exist', 'schema'];
    }

    /**
     * @Route(
     *    "/upload/{workspaceId}",
     *    name="apiv2_transfer_upload_file"
     * )
     * @ParamConverter("organization", options={"mapping": {"id": "uuid"}})
     * @Method("POST")
     *
     * @param Request $request
     */
    public function uploadFileAction(Request $request, $workspaceId = 0)
    {
        $file = $this->uploadFile($request);
        $workspace = $this->om->getRepository(Workspace::class)->find($workspaceId);
        if ($workspace) {
            $workspace = $this->serializer->serialize($workspace);
        }

        $this->crud->create(
            File::class,
            ['uploadedFile' => $file, 'workspace' => $workspace]
        );

        return new JsonResponse([$file], 200);
    }

    /**
     * @Route(
     *    "/workspace/{workspaceId}",
     *    name="apiv2_workspace_transfer_list"
     * )
     * @Method("GET")
     *
     * @param Request $request
     */
    public function workspaceListAction($workspaceId, Request $request)
    {
        $query = $request->query->all();
        $options = $this->options['list'];

        if (isset($query['options'])) {
            $options = $query['options'];
        }

        $query['hiddenFilters'] = ['workspace' => $workspaceId];

        return new JsonResponse($this->finder->search(
          self::getClass(),
          $query,
          $options
      ));
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
        $file = $data['file'];
        unset($data['file']);
        $action = $data['action'];
        unset($data['action']);

        $publicFile = $this->om->getObject($file, PublicFile::class) ?? new PublicFile();
        $uuid = $request->get('workspace');
        $workspace = $this->om->getRepository(Workspace::class)->findOneByUuid($uuid);

        if ($workspace) {
            $data['workspace'] = $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
        }

        $this->container->get('claroline.manager.api_manager')->import(
            $publicFile,
            $action,
            $this->getLogFile($request),
            $data
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
              PublicFile::class,
              [],
              ['file' => $file]
          );

        $dispatcher->dispatch(strtolower('upload_file_'.$handler), 'File\UploadFile', [$object]);

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
