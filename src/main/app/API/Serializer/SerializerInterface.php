<?php

namespace Claroline\AppBundle\API\Serializer;

interface SerializerInterface
{
    /**
     * Options : Only serialize data required to render lists.
     */
    public const SERIALIZE_LIST = 'serialize_list';

    /**
     * Options : Only serialize the minimal representation of data.
     * Common fields in minimal data : id, name, code, slug, thumbnail, description.
     */
    public const SERIALIZE_MINIMAL = 'serialize_minimal';

    /**
     * Options : Only serialize data required for transfer/copy features.
     * You should exclude all data related to the current user (like permissions).
     */
    public const SERIALIZE_TRANSFER = 'serialize_transfer';

    /**
     * Options : Expose absolute URLs in serialized data.
     */
    public const ABSOLUTE_URL = 'absolute_url';

    /**
     * Options : Generate new UUIDs when deserializing data into objects.
     * This is used anytime we want to copy existing data.
     */
    public const REFRESH_UUID = 'refresh_uuid';

    /**
     * Get the FQCN of the object managed by the serializer instance.
     */
    public static function getClass(): string;

    /**
     * Get the path to the JSONSchema file for the object.
     * It must be located in src/(main|plugin)/my-plugin/Resources/schemas.
     */
    public static function getSchema(): ?string;

    /**
     * Get the path to the directory containing samples data (for tests and documentation).
     */
    public static function getSamples(): ?string;

    public function serialize(object $object, ?array $options = []): array;

    public function deserialize(array $data, object $object, ?array $options = []): object;
}
