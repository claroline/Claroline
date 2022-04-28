<?php

namespace Claroline\AppBundle\API\Serializer;

interface SerializerInterface
{
    public static function getClass(): string;

    public static function getName(): string;

    public static function getSchema(): ?string;

    public static function getSamples(): ?string;
}
