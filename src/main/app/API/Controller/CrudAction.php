<?php

namespace Claroline\AppBundle\API\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;

trait CrudAction {
    abstract private function getCrud(): Crud;
    abstract private function getSerializer(): SerializerProvider;

    abstract public static function getClass(): string;
    abstract public static function getOptions(): array;
}
