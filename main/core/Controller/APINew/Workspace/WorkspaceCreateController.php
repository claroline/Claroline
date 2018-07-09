<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
  * @Route("/workspace/create")
  */
 class WorkspaceCreateController
 {
     /**
      * @DI\InjectParams({
      *     "serializer"  = @DI\Inject("claroline.api.serializer"),
      *     "crud"  = @DI\Inject("claroline.api.crud")
      * })
      */
     public function __construct(
        SerializerProvider $serializer,
        Crud $crud
     ) {
         $this->serializer = $serializer;
         $this->crud = $crud;
     }

     /**
      * @Route(
      *    "/base",
      *    name="apiv2_workspace_create_base"
      * )
      * @Method("POST")
      *
      * @return JsonResponse
      */
     public function createBaseAction()
     {
         $workspace = 'xxx';

         return new JsonResponse($this->serializer->serialize($workspace));
     }

     public function createRolesActions()
     {
     }

     public function createRootAction()
     {
     }

     public function createDirectoryAction()
     {
     }

     public function createResourceAction()
     {
     }

     public function duplicateOrderedToolsActions()
     {
     }
 }
