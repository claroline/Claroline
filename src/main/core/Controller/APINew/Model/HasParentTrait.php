<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

trait HasParentTrait
{
    /**
     * @Route("/{parent}/move/{child}", methods={"PATCH"})
     */
    public function moveAction($child, $parent, $class, Request $request)
    {
        $child = $this->crud->get($class, $child);
        $parent = $this->crud->get($class, $parent);

        $this->crud->replace($child, 'parent', $parent);

        return new JsonResponse($this->serializer->serialize($child));
    }
}
