<?php

namespace Claroline\ExampleBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ExampleBundle\Entity\Example;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/example', name: 'apiv2_example_')]
class ExampleController extends AbstractCrudController
{
    public static function getName(): string
    {
        return 'example';
    }

    public static function getClass(): string
    {
        return Example::class;
    }
}
