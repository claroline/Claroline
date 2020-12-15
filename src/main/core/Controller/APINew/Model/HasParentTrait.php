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
        $child = $this->find($class, $child);
        $parent = $this->find($class, $parent);
        $this->crud->replace($child, 'parent', $parent);

        return new JsonResponse($this->serializer->serialize($child));
    }
}
