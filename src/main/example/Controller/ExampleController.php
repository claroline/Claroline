<?php

namespace Claroline\ExampleBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ExampleBundle\Entity\Example;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/example")
 */
class ExampleController extends AbstractCrudController
{
    public function getName(): string
    {
        return 'example';
    }

    public function getClass(): string
    {
        return Example::class;
    }
}
