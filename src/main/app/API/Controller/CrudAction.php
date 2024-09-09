<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;

trait CrudAction
{
    abstract protected function getCrud(): Crud;

    abstract protected function getSerializer(): SerializerProvider;

    abstract public static function getClass(): string;

    abstract public static function getOptions(): array;
}
