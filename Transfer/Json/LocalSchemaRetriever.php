<?php

namespace UJM\ExoBundle\Transfer\Json;

use JsonSchema\Uri\Retrievers\FileGetContents;

/**
 * Specialized retriever for JsonSchema\Uri\UriRetriever, allowing
 * to retrieve schemas from local file system instead of fetching
 * them via HTTP requests.
 */
class LocalSchemaRetriever extends FileGetContents
{
    const BASE_URI = 'http://json-quiz.github.io/json-quiz/schemas';

    private $schemaDir;

    public function __construct()
    {
        $vendorDir = __DIR__ . '/../../../../../..';
        $this->schemaDir = realpath("{$vendorDir}/json-quiz/json-quiz/format");
    }

    /**
     * Builds a complete URI from the smallest part of a schema
     * path that can identify it without ambiguity.
     *
     * @param string $pathId
     * @return string
     */
    public static function uriFromPathId($pathId)
    {
        return self::BASE_URI . "/{$pathId}/schema.json";
    }

    /**
     * Retrieves the schema from the local file system if possible,
     * otherwise delegates to the original implementation.
     *
     * @param string $uri
     * @return string
     */
    public function retrieve($uri)
    {
        if (0 === strpos($uri, self::BASE_URI)) {
            $localPath = "{$this->schemaDir}/" . substr($uri, strlen(self::BASE_URI) + 1);

            return file_get_contents($localPath);
        }

        return parent::retrieve($uri);
    }
}
